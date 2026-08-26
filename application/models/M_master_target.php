<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_master_target extends CI_Model
{
    private $table = 'm_indikator';

    private $column_order = array('i.urut','i.kode','i.nama','i.tipe','t.target',null);
    private $column_search = array('i.kode','i.nama');
    private $order = array('i.urut'=>'ASC');

    private function _get_query($tahun)
    {
        $this->db->select("
            i.idindikator,
            i.kode,
            i.nama,
            i.tipe,
            i.urut,
            t.idtarget,
            t.target,
            t.subindikator
            ");

        $this->db->from('m_indikator i');

        $this->db->join(
            'm_target t',
            "t.idindikator=i.idindikator
            AND t.tahun=".$this->db->escape($tahun)."
            AND IFNULL(t.subindikator,'')=''",
            'left'
        );
        $this->db->where('i.aktif',1);

        if(!empty($_POST['search']['value'])) {
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

    public function get_datatables($tahun)
    {
        $this->_get_query($tahun);

        if($_POST['length']!=-1) {
            $this->db->limit(
                $_POST['length'],
                $_POST['start']
            );
        }
        return $this->db->get()->result();
    }

    public function count_filtered($tahun)
    {
        $this->_get_query($tahun);
        return $this->db->get()->num_rows();
    }

    public function count_all()
    {
        return $this->db
        ->where('aktif',1)
        ->count_all_results('m_indikator');
    }

    public function simpan_target($tahun,$idindikator,$target,$created_by)
    {
        $cek = $this->db
        ->where('tahun',$tahun)
        ->where('idindikator',$idindikator)
        ->where("IFNULL(subindikator,'')=''",NULL,FALSE)
        ->get('m_target')
        ->row();

        if($cek) {
            $this->db
            ->where('idtarget',$cek->idtarget)
            ->update('m_target',array(
                'target'=>$target
            ));
        }
        else {
            $this->db->insert('m_target',array(
                'idtarget'=>uuid_v4(),
                'tahun'=>$tahun,
                'idindikator'=>$idindikator,
                'subindikator'=>'',
                'target'=>$target,
                'created_by'=>$created_by
            ));
        }
    }

    public function get_multi($tahun,$idindikator)
    {
        return $this->db
        ->where('tahun',$tahun)
        ->where('idindikator',$idindikator)
        ->order_by('subindikator','ASC')
        ->get('m_target')
        ->result();
    }

    public function simpan_multi($data)
    {
        return $this->db->insert('m_target',$data);
    }

    public function hapus_multi($idtarget)
    {
        return $this->db
        ->where('idtarget',$idtarget)
        ->delete('m_target');
    }

    public function copy_tahun($tahun_asal,$tahun_tujuan,$user)
    {
        $this->db
        ->where('tahun',$tahun_tujuan)
        ->delete('m_target');

        $data = $this->db
        ->where('tahun',$tahun_asal)
        ->get('m_target')
        ->result();

        foreach($data as $r) {
            $this->db->insert('m_target',array(
                'idtarget'=>uuid_v4(),
                'tahun'=>$tahun_tujuan,
                'idindikator'=>$r->idindikator,
                'subindikator'=>$r->subindikator,
                'target'=>$r->target,
                'created_by'=>$user
            ));
        }
    }
}
