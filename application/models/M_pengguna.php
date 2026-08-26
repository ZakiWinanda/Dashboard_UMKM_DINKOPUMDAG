<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pengguna extends CI_Model
{
    private $table='m_users';

    private $column_order=array(null, 'nik','nama_lengkap','role','no_tlp','aktif');
    private $column_search=array('nik','nama_lengkap','role');
    private $order=array('nama_lengkap'=>'ASC');

    private function _get_query()
    {
        $nip = $this->session->userdata('monev_swk')['nip'];
        $this->db->from($this->table);
        $this->db->where('nik !=', $nip);
        $this->db->where('nik !="cakros"');
        if($_POST['search']['value']) {
            $this->db->group_start();
            foreach($this->column_search as $i=>$item) {
                if($i==0)
                    $this->db->like($item,$_POST['search']['value']);
                else
                    $this->db->or_like($item,$_POST['search']['value']);
            }
            $this->db->group_end();
        }

        if(isset($_POST['order'])) {
            $this->db->order_by(
                $this->column_order[$_POST['order'][0]['column']],
                $_POST['order'][0]['dir']
            );
        }
        else {
            foreach($this->order as $k=>$v) {
                $this->db->order_by($k,$v);
            }
        }
    }

    public function get_datatables()
    {
        $this->_get_query();
        if($_POST['length']!=-1) {
            $this->db->limit($_POST['length'],$_POST['start']);
        }

        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_query();
        return $this->db->get()->num_rows();
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

	public function get_pendamping()
	{
		return $this->db
				->select('nik, nama_lengkap')
				->where('role','pendamping')
				->where('aktif',1)
				->order_by('nama_lengkap')
				->get('m_users')
				->result();
	}
}
