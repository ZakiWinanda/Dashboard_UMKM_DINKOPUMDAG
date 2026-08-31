<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Capaian_bulanan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_capaian_harian');
        $this->load->model('M_kecamatan');
        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $monev_swk = $this->session->userdata('monev_swk');
        $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');
        $role      = $monev_swk['role'] ?? $this->session->userdata('role');

        if (empty($this->is_pendamping_kecamatan) && $role == 'pendamping') {
            redirect('capaian_harian');
            return;
        }

        $raw_kec = $this->M_kecamatan->get_kecamatan_by_user($nip, $role);
        $list_swk = [];
        foreach ($raw_kec as $k) {
            $nama_k = is_object($k) ? $k->nama_kecamatan : ($k['nama_kecamatan'] ?? $k);
            $list_swk[] = [
                'id'       => $nama_k,
                'idswk'    => $nama_k,
                'nama_swk' => $nama_k
            ];
        }

        $data['list_swk']   = $list_swk;
        $data['swk']        = $list_swk;
        $data['pendamping'] = ($role == 'administrator' || $role == 'pimpinan' || $role == 'koordinator_pendamping') ? $this->M_pengguna->get_pendamping() : [];

        $data['nip']   = $nip;
        $data['role']  = $role;
        $data['title'] = "REKAP OMSET & KUNJUNGAN BULANAN";
        $data['is_bulanan'] = true;
        $data['is_pendamping_kecamatan'] = true;

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
            $this->load->view('capaian_harian/koordinator_pendamping/script', $data);
        } else {
            $this->load->view('capaian_harian/pimpinan/index', $data);
            $this->load->view('footer');
        }
    }

    public function load_data()
    {
        $idswk   = trim((string)$this->input->post('idswk'));
        $periode = trim((string)$this->input->post('filter_bulan_tahun'));

        $monev_swk = $this->session->userdata('monev_swk');
        $nip       = $monev_swk['nip'] ?? $this->session->userdata('nip');
        $role      = $monev_swk['role'] ?? $this->session->userdata('role');

        if (empty($idswk)) {
            $list_kec = $this->M_kecamatan->get_kecamatan_by_user($nip, $role);
            if (!empty($list_kec)) {
                $first = current($list_kec);
                $idswk = is_object($first) ? $first->nama_kecamatan : ($first['nama_kecamatan'] ?? $first);
            }
        }

        if (empty($periode)) {
            $periode = date('m-Y');
        }

        $pecah = explode('-', $periode);
        $bulan = isset($pecah[0]) ? (int)$pecah[0] : (int)date('m');
        $tahun = isset($pecah[1]) ? (int)$pecah[1] : (int)date('Y');

        $omset_bulanan     = $this->M_capaian_harian->getOmsetBulanan($idswk, $tahun);
        $kunjungan_bulanan = $this->M_capaian_harian->getKunjunganBulanan($idswk, $tahun);

        $total_omset     = 0;
        $total_kunjungan = 0;
        if (!empty($omset_bulanan)) {
            foreach ($omset_bulanan as $r) {
                $total_omset += (float)$r->omset;
            }
        }
        if (!empty($kunjungan_bulanan)) {
            foreach ($kunjungan_bulanan as $r) {
                $total_kunjungan += (int)$r->jumlah;
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status'                 => TRUE,
                'is_kecamatan'           => TRUE,
                'idswk_terpilih'         => $idswk,
                'tahun'                  => $tahun,
                'bulan'                  => $bulan,
                'total_omset_harian'     => $total_omset,
                'total_kunjungan_harian' => $total_kunjungan,
                'omset'                  => $omset_bulanan,
                'kunjungan'              => $kunjungan_bulanan,
            )));
    }
}
