<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_pendamping extends CI_Model
{
    private $table = 'm_users';

    private $column_order = array(null,'u.nik','u.nama_lengkap',null,null);
    private $column_search = array('u.nik','u.nama_lengkap');
    private $order = array('u.nama_lengkap' => 'ASC');

private function _get_datatables_query()
{
    $this->db->select("
        u.nik,
        u.nama_lengkap,
        u.no_tlp,
        COUNT(DISTINCT ps.idswk) AS jumlah_swk,
        GROUP_CONCAT(
            s.nama_swk
            ORDER BY s.nama_swk
            SEPARATOR '<br>'
        ) AS daftar_swk
    ", FALSE);

    $this->db->from('m_users u');

    $this->db->join(
        'pendamping_swk ps',
        'ps.nip = u.nik',
        'left'
    );

    $this->db->join(
        'm_swk s',
        's.idswk = ps.idswk',
        'left'
    );

    $this->db->where('u.role','pendamping');
    $this->db->group_by(array(
        'u.nik',
        'u.nama_lengkap',
    ));

    if(isset($_POST['search']['value']) && $_POST['search']['value']!='') {
        $keyword=$_POST['search']['value'];

        $this->db->group_start();
        $this->db->like('u.nik',$keyword);
        $this->db->or_like('u.nama_lengkap',$keyword);
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
        $this->_get_datatables_query();

        $length = (int)$this->input->post('length');
        $start  = (int)$this->input->post('start');

        if($length != -1)
        {
            $this->db->limit($length,$start);
        }

        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    public function count_all()
    {
        $this->db->from('m_users');
        $this->db->where('role','pendamping');
        return $this->db->count_all_results();
    }
}
