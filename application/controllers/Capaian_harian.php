<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Capaian_harian extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_swk');
        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $monev_swk = $this->session->userdata('monev_swk');
        $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');
        $role      = $monev_swk['role'] ?? $this->session->userdata('role');

        // 1. Ambil list SWK berdasarkan NIP user yang login
        $list_swk = $this->M_swk->get_swk_by_user($nip, $role);
        $data['list_swk']   = $list_swk;
        $data['swk']        = $list_swk;
        $data['pendamping'] = ($role == 'administrator' || $role == 'pimpinan' || $role == 'koordinator_pendamping') ? $this->M_pengguna->get_pendamping() : [];

        // 2. Data pendukung ke view
        $data['nip']   = $nip;
        $data['role']  = $role;
        $data['title'] = "OMSET & KUNJUNGAN HARIAN";

        // 3. Load View sesuai Role
        $this->load->view('header', $data);

        if ($role == 'pendamping') {
            $this->load->view('capaian_harian/pendamping/index', $data);
            $this->load->view('footer');
            $this->load->view('capaian_harian/pendamping/script', $data);

        } elseif ($role == 'koordinator_pendamping') {
            $this->load->view('capaian_harian/koordinator_pendamping/index', $data);
            $this->load->view('footer');
            $this->load->view('capaian_harian/koordinator_pendamping/script', $data);

        } elseif ($role == 'administrator') {
            $this->load->view('capaian_harian/administrator/index', $data);
            $this->load->view('footer');

        } else { // Role Pimpinan
            $this->load->view('capaian_harian/pimpinan/index', $data);
            $this->load->view('footer');
        }
    }

    public function load_data()
    {
        $idswk   = trim((string)$this->input->post('idswk'));
        $periode = trim((string)$this->input->post('filter_bulan_tahun'));

        if (empty($idswk)) {
            $monev_swk = $this->session->userdata('monev_swk');
            $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');
            $role      = $monev_swk['role'] ?? $this->session->userdata('role');
            $list_swk  = $this->M_swk->get_swk_by_user($nip, $role);
            if (!empty($list_swk)) {
                $first = current($list_swk);
                $idswk = is_array($first) ? ($first['idswk'] ?? $first['id'] ?? '') : ($first->idswk ?? $first->id ?? '');
            }
        }

        if (empty($periode)) {
            $periode = date('m-Y');
        }

        $pecah = explode('-', $periode);
        $bulan = isset($pecah[0]) ? (int)$pecah[0] : (int)date('m');
        $tahun = isset($pecah[1]) ? (int)$pecah[1] : (int)date('Y');

        $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $omset     = [];
        $kunjungan = [];
        $total_omset_harian     = 0;
        $total_kunjungan_harian = 0;

        if (!empty($idswk)) {
            $omset     = $this->M_capaian_harian->getOmsetHarian($idswk, $bulan, $tahun);
            $query_omset_debug = $this->db->last_query(); // Debug query

            $kunjungan = $this->M_capaian_harian->getKunjunganHarian($idswk, $bulan, $tahun);
            $query_kunjungan_debug = $this->db->last_query(); // Debug query

            if (!empty($omset)) {
                foreach ($omset as $r) {
                    $total_omset_harian += (float)$r->omset;
                }
            }

            if (!empty($kunjungan)) {
                foreach ($kunjungan as $r) {
                    $total_kunjungan_harian += (int)$r->jumlah;
                }
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'                 => TRUE,
                'idswk_terpilih'         => $idswk,
                'debug_query_omset'      => $query_omset_debug ?? '',
                'debug_query_kunjungan'  => $query_kunjungan_debug ?? '',
                'jumlah_hari'            => $jumlah_hari,
                'omset'                  => $omset,
                'kunjungan'              => $kunjungan,
                'total_omset_harian'     => $total_omset_harian,
                'total_kunjungan_harian' => $total_kunjungan_harian
            )));
    }

    
    public function save_omset()
    {
        $monev_swk = $this->session->userdata('monev_swk');
        $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');

        $idswk   = trim($this->input->post('idswk'));
        $tanggal = trim($this->input->post('tanggal'));
        
        // Membersihkan input agar murni berupa angka (menghapus Rp, spasi, titik, koma)
        $omset   = preg_replace('/\D/', '', (string)$this->input->post('omset'));

        if (empty($idswk) || empty($tanggal)) {
            echo json_encode([
                'status'  => FALSE,
                'message' => 'ID SWK atau Tanggal tidak valid.'
            ]);
            return;
        }

        $data = array(
            'idswk'      => $idswk,
            'tanggal'    => $tanggal,
            'omset'      => (int)$omset,
            'created_by' => $nip
        );

        // 1. Simpan/Update data harian
        $this->M_capaian_harian->saveOmset($data);

        // 2. Update summary bulanan jika ada method updatePerformOmset
        if (method_exists($this->M_capaian_harian, 'updatePerformOmset')) {
            $this->M_capaian_harian->updatePerformOmset($idswk, $tanggal);
        }

        // 3. Hitung total bulanan untuk SWK tersebut
        $total = 0;
        if (method_exists($this->M_capaian_harian, 'totalOmsetBulanan')) {
            $total = $this->M_capaian_harian->totalOmsetBulanan(
                $idswk,
                date('m', strtotime($tanggal)),
                date('Y', strtotime($tanggal))
            );
        }

        echo json_encode(array(
            'status'             => TRUE,
            'total_omset_harian' => $total
        ));
    }

    public function save_kunjungan()
    {
        $monev_swk = $this->session->userdata('monev_swk');
        $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');

        $idswk   = trim($this->input->post('idswk'));
        $tanggal = trim($this->input->post('tanggal'));
        $jumlah  = preg_replace('/\D/', '', (string)$this->input->post('jumlah'));

        if (empty($idswk) || empty($tanggal)) {
            echo json_encode([
                'status'  => FALSE,
                'message' => 'ID SWK atau Tanggal tidak valid.'
            ]);
            return;
        }

        $data = array(
            'idswk'      => $idswk,
            'tanggal'    => $tanggal,
            'jumlah'     => (int)$jumlah,
            'created_by' => $nip
        );

        // 1. Simpan/Update data harian
        $this->M_capaian_harian->saveKunjungan($data);

        // 2. Update summary bulanan jika ada method updatePerformKunjungan
        if (method_exists($this->M_capaian_harian, 'updatePerformKunjungan')) {
            $this->M_capaian_harian->updatePerformKunjungan($idswk, $tanggal);
        }

        // 3. Hitung total bulanan untuk SWK tersebut
        $total = 0;
        if (method_exists($this->M_capaian_harian, 'totalKunjunganBulanan')) {
            $total = $this->M_capaian_harian->totalKunjunganBulanan(
                $idswk,
                date('m', strtotime($tanggal)),
                date('Y', strtotime($tanggal))
            );
        }

        echo json_encode(array(
            'status'                 => TRUE,
            'total_kunjungan_harian' => $total
        ));
    }

    public function get_omset()
    {
        $idswk   = $this->input->post('idswk');
        $tanggal = $this->input->post('tanggal');
        echo json_encode(
            $this->M_capaian_harian->getOmsetByTanggal($idswk, $tanggal)
        );
    }

    public function get_kunjungan()
    {
        $idswk   = $this->input->post('idswk');
        $tanggal = $this->input->post('tanggal');
        echo json_encode(
            $this->M_capaian_harian->getKunjunganByTanggal($idswk, $tanggal)
        );
    }
}