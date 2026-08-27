<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input_harian extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_swk');
        $this->load->model('M_unit_usaha');
    }

    public function index()
    {
        if ($this->is_pimpinan || $this->is_admin) {
            $list_swk = $this->M_swk->get_all_as_array();
        } else {
            $list_swk = $this->M_swk->get_by_pendamping_array($this->nip);
        }

        $data = [
            'title'    => 'INPUT OMSET & KUNJUNGAN',
            'list_swk' => $list_swk,
        ];

        $this->load->view('header', $data);
        $this->load->view('input_harian/index', $data);
        $this->load->view('footer');
        $this->load->view('input_harian/script', $data);
    }

    /**
     * AJAX: Ambil daftar unit usaha dari API per SWK
     * POST: idswk (UUID di API), idswk_lokal (UUID lokal)
     */
    public function load_unit_usaha()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $idswk_api   = trim($this->input->post('idswk_api'));
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $periode     = trim($this->input->post('periode')); // YYYY-MM

        if (empty($idswk_lokal) && !empty($idswk_api)) {
            $idswk_lokal = $idswk_api;
        }

        // Jika idswk_api tidak dikirim atau nilainya sama dengan idswk_lokal, cari api_swk_id dari DB m_swk
        if (empty($idswk_api) || $idswk_api === $idswk_lokal) {
            $swk = $this->db->select('api_swk_id')->where('idswk', $idswk_lokal)->get('m_swk')->row();
            if ($swk && !empty($swk->api_swk_id)) {
                $idswk_api = $swk->api_swk_id;
            }
        }

        if (empty($idswk_api) || empty($periode)) {
            echo json_encode(['status' => false, 'message' => 'Parameter tidak lengkap.']);
            return;
        }

        $parts = explode('-', $periode);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));

        // 1. Ambil unit usaha dari API
        $units = $this->M_unit_usaha->get_from_api($idswk_api);

        if (empty($units)) {
            echo json_encode(['status' => false, 'message' => 'Tidak ada unit usaha aktif untuk SWK ini.']);
            return;
        }

        // 2. Ambil data omset & kunjungan dari DB lokal
        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);
        $jumlah_hari   = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        // 3. Total per SWK
        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'          => true,
                'tahun'           => $tahun,
                'bulan'           => $bulan,
                'jumlah_hari'     => $jumlah_hari,
                'units'           => $units,
                'omset'           => $omset_map,
                'kunjungan'       => $kunjungan_map,
                'total_omset'     => $total_omset,
                'total_kunjungan' => $total_kunjungan,
            ]));
    }

    /**
     * AJAX: Simpan omset 1 unit usaha untuk 1 hari
     */
    public function save_omset_unit()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $monev = $this->session->userdata('monev_swk');
        $nip   = $monev['nip'] ?? '';

        $id_unit      = trim($this->input->post('id_unit_usaha'));
        $nama_unit    = trim($this->input->post('nama_unit_usaha'));
        $kode_unit    = trim($this->input->post('kode_unit_usaha'));
        $nama_stand   = trim($this->input->post('nama_stand'));
        $idswk        = trim($this->input->post('idswk'));
        $tanggal      = trim($this->input->post('tanggal'));
        $omset        = (int)preg_replace('/\D/', '', (string)$this->input->post('omset'));

        if (empty($id_unit) || empty($idswk) || empty($tanggal)) {
            echo json_encode(['status' => false, 'message' => 'Data tidak lengkap.']);
            return;
        }

        $this->M_unit_usaha->saveOmset([
            'id_unit_usaha'  => $id_unit,
            'nama_unit_usaha'=> $nama_unit,
            'kode_unit_usaha'=> $kode_unit,
            'nama_stand'     => $nama_stand,
            'idswk'          => $idswk,
            'tanggal'        => $tanggal,
            'omset'          => $omset,
            'created_by'     => $nip,
        ]);

        $parts = explode('-', $tanggal);
        $total = $this->M_unit_usaha->totalOmsetSwkBulan($idswk, (int)$parts[1], (int)$parts[0]);

        echo json_encode([
            'status'  => true,
            'message' => 'Omset disimpan.',
            'total'   => $total,
        ]);
    }

    /**
     * AJAX: Simpan kunjungan 1 unit usaha untuk 1 hari
     */
    public function save_kunjungan_unit()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $monev = $this->session->userdata('monev_swk');
        $nip   = $monev['nip'] ?? '';

        $id_unit      = trim($this->input->post('id_unit_usaha'));
        $nama_unit    = trim($this->input->post('nama_unit_usaha'));
        $kode_unit    = trim($this->input->post('kode_unit_usaha'));
        $nama_stand   = trim($this->input->post('nama_stand'));
        $idswk        = trim($this->input->post('idswk'));
        $tanggal      = trim($this->input->post('tanggal'));
        $jumlah       = (int)preg_replace('/\D/', '', (string)$this->input->post('jumlah'));

        if (empty($id_unit) || empty($idswk) || empty($tanggal)) {
            echo json_encode(['status' => false, 'message' => 'Data tidak lengkap.']);
            return;
        }

        $this->M_unit_usaha->saveKunjungan([
            'id_unit_usaha'  => $id_unit,
            'nama_unit_usaha'=> $nama_unit,
            'kode_unit_usaha'=> $kode_unit,
            'nama_stand'     => $nama_stand,
            'idswk'          => $idswk,
            'tanggal'        => $tanggal,
            'jumlah'         => $jumlah,
            'created_by'     => $nip,
        ]);

        $parts = explode('-', $tanggal);
        $total = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk, (int)$parts[1], (int)$parts[0]);

        echo json_encode([
            'status'  => true,
            'message' => 'Kunjungan disimpan.',
            'total'   => $total,
        ]);
    }
}
