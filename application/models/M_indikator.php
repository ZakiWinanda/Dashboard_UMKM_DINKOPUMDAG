<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_indikator extends CI_Model
{
    protected $table = 'm_indikator';
    private $column_order = array(null,'kode','nama','tipe','aktif',null);
    private $column_search = array('kode','nama','tipe');
    private $order = array('urut' => 'ASC','kode' => 'ASC');

    private function _get_datatables_query()
    {
        $this->db->select("
            i.*,
            COUNT(t.idtarget) jumlah_target
        ");
        $this->db->from('m_indikator i');
        $this->db->join(
            'm_target t',
            't.idindikator=i.idindikator',
            'left'
        );
        $this->db->group_by('i.idindikator');

        $i = 0;
        foreach ($this->column_search as $item) {
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
            foreach($this->order as $k=>$v) {
                $this->db->order_by($k,$v);
            }
        }
    }

    function get_datatables()
    {
        $this->_get_datatables_query();

        if($_POST['length'] != -1)
        {
            $this->db->limit(
                $_POST['length'],
                $_POST['start']
            );
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

    function get_all()
    {
        $this->db->select("
            i.*,
            COUNT(t.idtarget) jumlah_target
        ");

        $this->db->from('m_indikator i');

        $this->db->join(
            'm_target t',
            't.idindikator=i.idindikator',
            'left'
        );

        $this->db->group_by('i.idindikator');
        $this->db->order_by('urut','ASC');

        return $this->db->get()->result();
    }

    function get_by_id($id)
    {
        return $this->db
            ->where('idindikator',$id)
            ->get($this->table)
            ->row();
    }

    function get_by_kode($kode)
    {
        return $this->db
            ->where('kode',$kode)
            ->get($this->table)
            ->row();
    }

    function insert($data)
    {
        return $this->db->insert(
            $this->table,
            $data
        );
    }

    function update($id,$data)
    {
        return $this->db
            ->where('idindikator',$id)
            ->update(
                $this->table,
                $data
            );
    }

    function delete($id)
    {
        return $this->db
            ->where('idindikator',$id)
            ->delete($this->table);
    }

    function aktifkan($id)
    {
        return $this->db
            ->where('idindikator',$id)
            ->update(
                $this->table,
                array(
                    'aktif'=>1
                )
            );
    }

    function nonaktifkan($id)
    {
        return $this->db
            ->where('idindikator',$id)
            ->update(
                $this->table,
                array(
                    'aktif'=>0
                )
            );
    }

    function get_aktif()
    {
        return $this->db
            ->where('aktif',1)
            ->order_by('urut')
            ->order_by('kode')
            ->get($this->table)
            ->result();
    }

    function dropdown()
    {
        $data=array();

        $this->db
            ->where('aktif',1)
            ->order_by('urut')
            ->order_by('kode');

        foreach($this->db->get($this->table)->result() as $r)
        {
            $data[$r->idindikator]=$r->kode.' - '.$r->nama;
        }

        return $data;
    }

    function cek_kode($kode,$id='')
    {
        $this->db->where('kode',$kode);

        if($id!='')
        {
            $this->db->where(
                'idindikator <>',
                $id
            );
        }

        return $this->db
            ->get($this->table)
            ->num_rows();
    }

    public function get_by_pendamping($idpendamping)
    {
        return $this->db
                ->select('s.idswk,s.nama_swk')
                ->from('m_swk s')
                ->join('m_pendamping_swk p','p.idswk=s.idswk')
                ->where('p.iduser',$idpendamping)
                ->where('s.aktif',1)
                ->order_by('s.nama_swk','ASC')
                ->get()
                ->result();
    }

    public function get_idindikator($kode)
    {
        return $this->db
            ->from ('m_indikator')
            ->where('kode', $kode)
            ->get()
            ->row('idindikator');
    }

}
