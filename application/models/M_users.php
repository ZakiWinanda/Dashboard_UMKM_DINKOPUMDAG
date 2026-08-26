<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_users extends CI_Model
{
    protected $table = 'm_users';
    protected $column_order = array(null, 'nik', 'nama_lengkap', 'no_tlp', 'aktif');
    protected $column_search = array('nik', 'nama_lengkap', 'no_tlp');
    protected $default_order = array('nama_lengkap' => 'ASC');

    private function _get_datatables_query()
    {
        $post = $this->input->post(NULL, TRUE);
        $this->db->from($this->table);
        $this->db->where('aktif', 1);

        if (!empty($post['search']['value']))
        {
            $keyword = trim($post['search']['value']);
            $this->db->group_start();

            foreach ($this->column_search as $i => $column)
            {
                if ($i == 0) {
                    $this->db->like($column, $keyword);
                }
                else {
                    $this->db->or_like($column, $keyword);
                }
            }
            $this->db->group_end();
        }

        if (isset($post['order'][0])) {
            $column_index = (int)$post['order'][0]['column'];
            $direction    = strtolower($post['order'][0]['dir']);
            $direction = ($direction === 'desc') ? 'DESC' : 'ASC';

            if (isset($this->column_order[$column_index]) && !empty($this->column_order[$column_index])) {
                $this->db->order_by(
                    $this->column_order[$column_index],
                    $direction
                );
            }
            else {
                foreach ($this->default_order as $col => $dir) {
                    $this->db->order_by($col, $dir);
                }
            }
        }
        else {
            foreach ($this->default_order as $col => $dir) {
                $this->db->order_by($col, $dir);
            }
        }
    }

    public function get_datatables()
    {
        $post = $this->input->post(NULL, TRUE);
        $this->_get_datatables_query();

        $length = isset($post['length']) ? (int)$post['length'] : 10;
        $start  = isset($post['start'])  ? (int)$post['start']  : 0;

        if ($length > 100) {
            $length = 100;
        }

        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    public function get_all()
    {
        return $this->db
            ->select('nik,nama_lengkap')
            ->where('aktif', 1)
            ->order_by('nama_lengkap','ASC')
            ->get('m_users')
            ->result();
    }

}