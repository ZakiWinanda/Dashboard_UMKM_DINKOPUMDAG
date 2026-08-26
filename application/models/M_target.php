<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_target extends CI_Model
{

    private $table = 'm_target';

    function get_all($idindikator)
    {
        return $this->db
        ->where('idindikator',$idindikator)
        ->order_by('tahun','DESC')
        ->order_by('urut','ASC')
        ->get($this->table)
        ->result();
    }

    function get_by_id($idtarget)
    {
        return $this->db
        ->where('idtarget',$idtarget)
        ->get($this->table)
        ->row();
    }

    function insert($data)
    {
        return $this->db->insert($this->table,$data);
    }

    function update($idtarget,$data)
    {
        return $this->db
        ->where('idtarget',$idtarget)
        ->update($this->table,$data);
    }

    function delete($idtarget)
    {
        return $this->db
        ->where('idtarget',$idtarget)
        ->delete($this->table);
    }

    function jumlah_target($idindikator)
    {
        return $this->db
        ->where('idindikator',$idindikator)
        ->count_all_results($this->table);
    }

}
