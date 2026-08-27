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
            'title'    => 'INPUT OMSET & KUNJUNGAN (EXCEL)',
            'list_swk' => $list_swk,
        ];

        $this->load->view('header', $data);
        $this->load->view('input_harian/index', $data);
        $this->load->view('footer');
        $this->load->view('input_harian/script', $data);
    }

    /**
     * AJAX: Ambil daftar unit usaha & data bulanan per SWK
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

        $units = $this->M_unit_usaha->get_from_api($idswk_api);

        if (empty($units)) {
            echo json_encode(['status' => false, 'message' => 'Tidak ada unit usaha aktif untuk SWK ini.']);
            return;
        }

        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);
        $jumlah_hari   = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

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
     * Download Template CSV/Excel terisi data unit usaha per SWK
     */
    public function download_template()
    {
        $idswk_lokal = trim($this->input->get('idswk_lokal'));
        $idswk_api   = trim($this->input->get('idswk_api'));
        $periode     = trim($this->input->get('periode'));

        if (empty($idswk_api) || $idswk_api === $idswk_lokal) {
            $swkRow = $this->db->select('api_swk_id, nama_swk')->where('idswk', $idswk_lokal)->get('m_swk')->row();
            if ($swkRow) {
                if (!empty($swkRow->api_swk_id)) $idswk_api = $swkRow->api_swk_id;
                $nama_swk = $swkRow->nama_swk;
            }
        }

        if (empty($periode)) $periode = date('Y-m');

        $parts = explode('-', $periode);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));
        $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $units         = $this->M_unit_usaha->get_from_api($idswk_api);
        $omset_map     = $this->M_unit_usaha->getOmsetBulan($idswk_lokal, $bulan, $tahun);
        $kunjungan_map = $this->M_unit_usaha->getKunjunganBulan($idswk_lokal, $bulan, $tahun);

        $filename = "Template_Input_Harian_" . preg_replace('/[^A-Za-z0-9_]/', '', $periode) . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($output, "\xEF\xBB\xBF");

        // Header CSV
        $header = ['ID_UNIT_USAHA', 'KODE_STAND', 'KODE_USAHA', 'NAMA_USAHA', 'NAMA_PEDAGANG', 'TIPE_DATA'];
        for ($d = 1; d <= $jumlah_hari; d++) {
            $header[] = 'TGL_' . str_pad($d, 2, '0', STR_PAD_LEFT);
        }
        fputcsv($output, $header, ';');

        // Data Rows per Unit
        foreach ($units as $u) {
            $uid = $u['id'];

            // Baris OMSET
            $rowOm = [
                $uid,
                $u['namaStand'],
                $u['kodeUsahaSwk'],
                $u['namaUsaha'],
                $u['namaPedagang'],
                'OMSET'
            ];
            for ($d = 1; d <= $jumlah_hari; d++) {
                $tgl = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $val = $omset_map[$uid][$tgl] ?? 0;
                $rowOm[] = $val;
            }
            fputcsv($output, $rowOm, ';');

            // Baris KUNJUNGAN
            $rowKj = [
                $uid,
                $u['namaStand'],
                $u['kodeUsahaSwk'],
                $u['namaUsaha'],
                $u['namaPedagang'],
                'KUNJUNGAN'
            ];
            for ($d = 1; d <= $jumlah_hari; d++) {
                $tgl = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                $val = $kunjungan_map[$uid][$tgl] ?? 0;
                $rowKj[] = $val;
            }
            fputcsv($output, $rowKj, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Upload & Process File Excel / CSV
     */
    public function upload_excel()
    {
        if (!$this->input->is_ajax_request()) show_404();

        $monev       = $this->session->userdata('monev_swk');
        $nip         = $monev['nip'] ?? '';
        $idswk_lokal = trim($this->input->post('idswk_lokal'));
        $periode     = trim($this->input->post('periode')); // YYYY-MM

        if (empty($idswk_lokal) || empty($periode)) {
            echo json_encode(['status' => false, 'message' => 'SWK dan Periode wajib dipilih.']);
            return;
        }

        if (empty($_FILES['file_excel']['tmp_name'])) {
            echo json_encode(['status' => false, 'message' => 'File Excel/CSV belum dipilih.']);
            return;
        }

        $parts = explode('-', $periode);
        $tahun = (int)($parts[0] ?? date('Y'));
        $bulan = (int)($parts[1] ?? date('m'));
        $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $tmpFile = $_FILES['file_excel']['tmp_name'];
        $handle  = fopen($tmpFile, 'r');

        if (!$handle) {
            echo json_encode(['status' => false, 'message' => 'Gagal membaca file upload.']);
            return;
        }

        // Cek BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Cek delimiter (, atau ;)
        $firstLine = fgets($handle);
        rewind($handle);
        if ($bom === "\xEF\xBB\xBF") fread($handle, 3);

        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

        // Read Header
        $header = fgetcsv($handle, 4096, $delimiter);
        if (!$header || count($header) < 6) {
            fclose($handle);
            echo json_encode(['status' => false, 'message' => 'Format header CSV/Excel tidak sesuai template.']);
            return;
        }

        // Normalisasi header
        $headerClean = array_map(function($h) {
            return strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', $h)));
        }, $header);

        $idCol   = array_search('ID_UNIT_USAHA', $headerClean);
        $typeCol = array_search('TIPE_DATA', $headerClean);
        $standCol= array_search('KODE_STAND', $headerClean);
        $nameCol = array_search('NAMA_USAHA', $headerClean);
        $kodeCol = array_search('KODE_USAHA', $headerClean);

        if ($idCol === false || $typeCol === false) {
            fclose($handle);
            echo json_encode(['status' => false, 'message' => 'Kolom ID_UNIT_USAHA atau TIPE_DATA tidak ditemukan di file.']);
            return;
        }

        // Petakan kolom hari
        $dayCols = [];
        foreach ($headerClean as $idx => $hName) {
            if (preg_match('/^TGL_?(\d{1,2})$/', $hName, $m)) {
                $dayNum = (int)$m[1];
                if ($dayNum >= 1 && $dayNum <= $jumlah_hari) {
                    $dayCols[$dayNum] = $idx;
                }
            }
        }

        $batchOmset     = [];
        $batchKunjungan = [];

        while (($row = fgetcsv($handle, 4096, $delimiter)) !== false) {
            if (count($row) < 6) continue;

            $uid   = trim($row[$idCol] ?? '');
            $tipe  = strtoupper(trim($row[$typeCol] ?? ''));
            $stand = trim($row[$standCol] ?? '');
            $nama  = trim($row[$nameCol] ?? '');
            $kode  = trim($row[$kodeCol] ?? '');

            if (empty($uid) || empty($tipe)) continue;

            foreach ($dayCols as $dayNum => $cIdx) {
                $rawVal = trim($row[$cIdx] ?? '0');
                $val    = (int)preg_replace('/[^\d]/', '', $rawVal);

                $tanggal = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayNum, 2, '0', STR_PAD_LEFT);

                if ($tipe === 'OMSET') {
                    $batchOmset[] = [
                        'id_unit_usaha'  => $uid,
                        'nama_unit_usaha'=> $nama,
                        'kode_unit_usaha'=> $kode,
                        'nama_stand'     => $stand,
                        'idswk'          => $idswk_lokal,
                        'tanggal'        => $tanggal,
                        'omset'          => $val,
                        'created_by'     => $nip,
                    ];
                } elseif ($tipe === 'KUNJUNGAN') {
                    $batchKunjungan[] = [
                        'id_unit_usaha'  => $uid,
                        'nama_unit_usaha'=> $nama,
                        'kode_unit_usaha'=> $kode,
                        'nama_stand'     => $stand,
                        'idswk'          => $idswk_lokal,
                        'tanggal'        => $tanggal,
                        'jumlah'         => $val,
                        'created_by'     => $nip,
                    ];
                }
            }
        }

        fclose($handle);

        $savedOmset = $this->M_unit_usaha->saveBatchOmset($batchOmset);
        $savedKj    = $this->M_unit_usaha->saveBatchKunjungan($batchKunjungan);

        $total_omset     = $this->M_unit_usaha->totalOmsetSwkBulan($idswk_lokal, $bulan, $tahun);
        $total_kunjungan = $this->M_unit_usaha->totalKunjunganSwkBulan($idswk_lokal, $bulan, $tahun);

        echo json_encode([
            'status'          => true,
            'message'         => 'Impor berhasil! Total ' . ($savedOmset + $savedKj) . ' entri data diperbarui.',
            'total_omset'     => $total_omset,
            'total_kunjungan' => $total_kunjungan,
        ]);
    }
}
