<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_kecamatan extends CI_Model
{
    protected $table = 'pendamping_kecamatan';

    public function get_all()
    {
        return $this->db
            ->select('DISTINCT(nama_kecamatan)')
            ->order_by('nama_kecamatan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_pendamping($nip)
    {
        return $this->db
            ->select('nip, nama_kecamatan')
            ->where('nip', $nip)
            ->order_by('nama_kecamatan', 'ASC')
            ->get($this->table)
            ->result();
    }

    public function get_by_koordinator($nip_koordinator)
    {
        return $this->db
            ->select('DISTINCT(pk.nama_kecamatan), pk.nip')
            ->from('koordinator_pendamping kp')
            ->join('pendamping_kecamatan pk', 'pk.nip = kp.nip_pendamping')
            ->where('kp.nip_koordinator', $nip_koordinator)
            ->order_by('pk.nama_kecamatan', 'ASC')
            ->get()
            ->result();
    }

    public function get_kecamatan_by_user($nip, $role = 'pendamping')
    {
        if ($role == 'administrator' || $role == 'pimpinan') {
            return $this->get_all();
        } elseif ($role == 'koordinator_pendamping') {
            return $this->get_by_koordinator($nip);
        } else {
            return $this->get_by_pendamping($nip);
        }
    }

    /**
     * Ambil data integrasi kecamatan per NIP langsung dari API eksternal
     */
    public function get_api_kecamatan($nip = null)
    {
        return $this->api_client->get_kecamatan_by_nip($nip);
    }

    /**
     * Ambil data integrasi legalitas Industri Rumahan per NIP dari API eksternal
     */
    public function get_api_legalitas_industri_rumahan($bulan, $tahun, $nip = null)
    {
        return $this->api_client->get_legalitas_industri_rumahan($bulan, $tahun, $nip);
    }

    /**
     * Ambil data integrasi kenaikan omzet Industri Rumahan per NIP dari API eksternal
     */
    public function get_api_omzet_industri_rumahan($bulan, $tahun, $nip = null)
    {
        return $this->api_client->get_omzet_industri_rumahan($bulan, $tahun, $nip);
    }
}
