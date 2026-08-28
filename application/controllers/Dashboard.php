<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_dashboard','dashboard');
        $this->load->model('M_dashboard_petugas','dashboard_petugas');
        $this->load->model('M_pengguna');
    }

    public function index()
    {
        $data = array(
            'title' => 'DASHBOARD',
            'tahun' => date('Y'),
            'bulan' => date('n')
        );

        $view = 'dashboard/'.$this->role;
        $data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : '';
        $this->load->view('header', $data);
        if(file_exists(APPPATH.'views/'.$view.'/index.php')){
            $this->load->view($view.'/index', $data);
        }
        else{
            show_error('View tidak ditemukan.');
        };
    }

    public function loadDashboard()
    {
        if($this->is_pimpinan || $this->is_admin) $this->_loadDashboard_pimpinan();
        else $this->_loadDashboard_petugas();
    }

    function _loadDashboard_pimpinan()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $tahun = (int) $this->input->post('tahun');
        $bulan = (int) $this->input->post('bulan');
        $pendamping = $this->input->post('pendamping');

        if ($tahun <= 0) {
            $tahun = date('Y');
        }

        if ($bulan <= 0 || $bulan > 12) {
            $bulan = date('n');
        }

        $nip = '';
        if (!empty($pendamping)) {
            $nip = $pendamping;
        }

        $data = array(
            'status'      => true,
            'tahun'       => $tahun,
            'bulan'       => $bulan,
            'total_swk'   => $this->dashboard_petugas->totalSwk($nip),
            'sudah'       => $this->dashboard_petugas->sudahLapor($nip, $tahun, $bulan),
            'belum'       => $this->dashboard_petugas->belumLapor($nip, $tahun, $bulan),
            'progress'    => $this->dashboard_petugas->progress($nip, $tahun, $bulan),
            'nilaikinerja'    => $this->dashboard_petugas->getNilaiKinerjaPendamping($nip, $tahun, $bulan),
            'swk'         => $this->dashboard_petugas->daftarSwk($nip, $tahun, $bulan),
            'monitoring'  => $this->dashboard_petugas->monitoringTerakhir($nip),
            'belum_lapor' => $this->dashboard_petugas->swkBelumLapor($nip, $tahun, $bulan),
            'last_update' => $this->dashboard_petugas->lastUpdate($nip, $tahun, $bulan)
        );

        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data, JSON_NUMERIC_CHECK));
    }

    function _loadDashboard_petugas()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $tahun = (int) $this->input->post('tahun');
        $bulan = (int) $this->input->post('bulan');
        if ($tahun <= 0) {
            $tahun = date('Y');
        }

        if ($bulan <= 0 || $bulan > 12) {
            $bulan = date('n');
        }

        $nip = '';
        if (isset($this->nip)) {
            $nip = $this->nip;
        }

        if ($nip == '') {

            $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'status' => false,
                'message' => 'Session login telah berakhir.'
            )));

            return;
        }

        $data = array(
            'status'      => true,
            'tahun'       => $tahun,
            'bulan'       => $bulan,
            'total_swk'   => $this->dashboard_petugas->totalSwk($nip),
            'sudah'       => $this->dashboard_petugas->sudahLapor($nip, $tahun, $bulan),
            'belum'       => $this->dashboard_petugas->belumLapor($nip, $tahun, $bulan),
            'progress'    => $this->dashboard_petugas->progress($nip, $tahun, $bulan),
            'nilaikinerja'    => $this->dashboard_petugas->getNilaiKinerjaPendamping($nip, $tahun, $bulan),
            'swk'         => $this->dashboard_petugas->daftarSwk($nip, $tahun, $bulan),
            'monitoring'  => $this->dashboard_petugas->monitoringTerakhir($nip)
        );

        $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($data, JSON_NUMERIC_CHECK));
    }

    public function ubahpassword()
    {
        $result = array(
            'status' => false,
            'pesan'  => ''
        );

        if (!$this->input->post()) {
            echo json_encode($result);
            return;
        }

        $username =  $this->nip;

        $passwordsekarang = trim($this->input->post('passwordsekarang', TRUE));
        $passwordbaru1    = trim($this->input->post('passwordbaru1', TRUE));
        $passwordbaru2    = trim($this->input->post('passwordbaru2', TRUE));

        if ($passwordsekarang == '' || $passwordbaru1 == '' || $passwordbaru2 == '') {
            $result['pesan'] = 'Semua field wajib diisi.';
            echo json_encode($result);
            return;
        }

        if ($passwordbaru1 != $passwordbaru2) {
            $result['pesan'] = 'Konfirmasi password baru tidak sesuai.';
            echo json_encode($result);
            return;
        }

        if (strlen($passwordbaru1) < 6) {
            $result['pesan'] = 'Password minimal 6 karakter.';
            echo json_encode($result);
            return;
        }

        $user = $this->db
        ->where('nik', $username)
        ->where('aktif', 1)
        ->get('m_users')
        ->row();

        if (!$user) {
            $result['pesan'] = 'User tidak ditemukan.';
            echo json_encode($result);
            return;
        }

        if (!password_verify($passwordsekarang, $user->pass)) {
            $result['pesan'] = 'Password lama tidak sesuai.';
            echo json_encode($result);
            return;
        }

        if (password_verify($passwordbaru1, $user->pass)) {
            $result['pesan'] = 'Password baru tidak boleh sama dengan password lama.';
            echo json_encode($result);
            return;
        }

        $update = array(
            'pass' => password_hash($passwordbaru1, PASSWORD_BCRYPT)
        );

        $this->db->where('nik', $username);
        $this->db->where('aktif', 1);

        if ($this->db->update('m_users', $update)) {
            $result['status'] = 'success';
            $result['pesan']  = 'Password berhasil diubah.';
        }
        else {
            $result['pesan'] = 'Password gagal diubah.';
        }

        echo json_encode($result);
    }

}
