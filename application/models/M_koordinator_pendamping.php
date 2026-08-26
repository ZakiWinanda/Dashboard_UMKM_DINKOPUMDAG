<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_koordinator_pendamping extends CI_Model
{
    var $table = 'koordinator_pendamping kp';
    var $column_order = array(null,'u1.nama_lengkap',null);
    var $column_search = array('u1.nama_lengkap','u2.nama_lengkap');
    var $order = array('u1.nama_lengkap' => 'ASC');

    private function _get_datatables_query()
    {
        $this->db->select("
            kp.nip_koordinator,
            u1.nama_lengkap AS nama_koordinator,
            COUNT(kp.nip_pendamping) AS jumlah_pendamping,
            GROUP_CONCAT(u2.nama_lengkap ORDER BY u2.nama_lengkap SEPARATOR '||') AS nama_pendamping
        ", FALSE);

        $this->db->from($this->table);

        $this->db->join('m_users u1', 'u1.nik = kp.nip_koordinator', 'left');
        $this->db->join('m_users u2', 'u2.nik = kp.nip_pendamping', 'left');

        $this->db->group_by(array(
            'kp.nip_koordinator',
            'u1.nama_lengkap'
        ));

        $i = 0;
        foreach ($this->column_search as $item) {
            if($_POST['search']['value']) {
                if($i===0) {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                }
                else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if(count($this->column_search)-1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if(isset($_POST['order'])) {
            $this->db->order_by(
                $this->column_order[$_POST['order']['0']['column']],
                $_POST['order']['0']['dir']
            );
        }
        else {
            foreach($this->order as $key=>$val) {
                $this->db->order_by($key,$val);
            }
        }
    }

    public function get_datatables()
    {
        $this->_get_datatables_query();

        if($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);

        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    public function count_all()
    {
        $this->db->select('COUNT(DISTINCT nip_koordinator) total');
        $this->db->from('koordinator_pendamping');

        return $this->db->get()->row()->total;
    }


	public function get_all_koordinator()
    {
        return $this->db
            ->select('
                kp.nip_pendamping,
                p.nama_lengkap as nama_pendamping
            ')
            ->from('koordinator_pendamping kp')
            ->join('m_users p', 'p.nik = kp.nip_pendamping')
            ->order_by('p.nama_lengkap', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_koordinator($nip_koordinator)
    {
        return $this->db
            ->select('
                kp.nip_pendamping as nik,
                p.nama_lengkap
            ')
            ->from('koordinator_pendamping kp')
            ->join('m_users p', 'p.nik = kp.nip_pendamping')
            ->where('kp.nip_koordinator', $nip_koordinator)
            ->order_by('p.nama_lengkap', 'ASC')
            ->get()
            ->result();
    }
}
