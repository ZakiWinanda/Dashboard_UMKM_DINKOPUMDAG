<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $user;
    protected $role;
    protected $nip;
    protected $nama;
    protected $is_koordinator_pendamping;
    protected $is_pimpinan;
    protected $is_admin;
    protected $is_pendamping_swk;
    protected $is_pendamping_kecamatan;

    public function __construct()
    {
        parent::__construct();

        $this->user = $this->session->userdata('monev_swk');

        if (empty($this->user)) {
            redirect('login');
            exit;
        }

        $this->nip  = $this->user['nip'];
        $this->nama = $this->user['nama'];
        $this->role = $this->user['role'];
        $this->is_pimpinan = $this->role=='pimpinan' ? true : false;
        $this->is_admin = $this->role=='administrator' ? true : false;

        $this->is_koordinator_pendamping = $this->db
        ->where('nip_koordinator', $this->nip)
        ->count_all_results('koordinator_pendamping') > 0;

        // Cek apakah NIP terdaftar sebagai Pendamping SWK
        $this->is_pendamping_swk = $this->db
        ->where('nip', $this->nip)
        ->count_all_results('pendamping_swk') > 0;

        // Jika role pendamping dan TIDAK terdaftar di pendamping_swk, maka adalah Pendamping Kecamatan
        $this->is_pendamping_kecamatan = (!$this->is_pendamping_swk && $this->role == 'pendamping');

        $this->load->vars(array(
            'user'                      => $this->user,
            'nama'                      => $this->nama,
            'nip'                       => $this->nip,
            'role'                      => $this->role,
            'is_pimpinan'               => $this->is_pimpinan,
            'is_admin'                  => $this->is_admin,
            'is_koordinator_pendamping' => $this->is_koordinator_pendamping,
            'is_pendamping_swk'         => $this->is_pendamping_swk,
            'is_pendamping_kecamatan'   => $this->is_pendamping_kecamatan
        ));
    }
}
