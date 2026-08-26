<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_omset extends CI_Model
{
    var $table = 'm_omset';
    var $column_order = array(null,'s.nama_swk','tahun','bulan','omset');
    var $column_search = array('s.nama_swk','tahun','bulan');
    var $order = array('s.nama_swk'=>'ASC', 'tahun'=>'DESC','bulan'=>'DESC');

    private function _get_datatables_query()
    {
        $this->db->select("
            o.*,
            s.nama_swk,
            s.alamat
        ");

        $this->db->from('m_omset o');
        $this->db->join('m_swk s','s.idswk=o.idswk');

        $i=0;
        foreach($this->column_search as $item) {
            if($_POST['search']['value']) {
                if($i===0) {
                    $this->db->group_start();
                    $this->db->like($item,$_POST['search']['value']);
                }
                else {
                    $this->db->or_like($item,$_POST['search']['value']);
                }

                if(count($this->column_search)-1==$i)
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
            foreach($this->order as $k=>$v)
                $this->db->order_by($k,$v);
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();

        if($_POST['length']!=-1) {
            $this->db->limit($_POST['length'],$_POST['start']);
        }
        return $this->db->get()->result();
    }

    function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    function count_all()
    {
        return $this->db->count_all($this->table);
    }

    function getById($id)
    {
        return $this->db
            ->where('idomset',$id)
            ->get($this->table)
            ->row();
    }

    function insert($data)
    {
        return $this->db->insert($this->table,$data);
    }

    function update($id,$data)
    {
        return $this->db
            ->where('idomset',$id)
            ->update($this->table,$data);
    }

    function delete($id)
    {
        return $this->db
            ->where('idomset',$id)
            ->delete($this->table);
    }

}
