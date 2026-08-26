<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_dashboard extends CI_Model
{

    public function totalSwk($pendamping = '')
    {
        $this->db->from('m_swk s');

        if($pendamping != '') {
            $this->db->join('pendamping_swk ps','ps.idswk=s.idswk');
            $this->db->where('ps.nip',$pendamping);
        }
        $this->db->where('s.aktif',1);
        return $this->db->count_all_results();
    }

    public function lastUpdate($tahun, $awal, $akhir, $pendamping = '')
    {
        $this->db->select('MAX(p.created_at) AS last_update');
        $this->db->from('t_perform p');
        $this->db->join('pendamping_swk ps', 'ps.idswk = p.idswk', 'left');

        $this->db->where('p.tahun', $tahun);
        $this->db->where('p.bulan >=', $awal);
        $this->db->where('p.bulan <=', $akhir);

        if (!empty($pendamping)) {
            $this->db->where('ps.nip', $pendamping);
        }
        $row = $this->db->get()->row();
        return ($row && !empty($row->last_update)) ? $row->last_update : NULL;
    }

    public function sudahLapor($tahun,$awal,$akhir,$pendamping='')
    {
        $this->db->select('COUNT(DISTINCT p.idswk) jumlah');
        $this->db->from('t_perform p');
        $this->db->join('pendamping_swk ps','ps.idswk=p.idswk','left');

        $this->db->where('p.tahun',$tahun);
        $this->db->where('p.bulan >=',$awal);
        $this->db->where('p.bulan <=',$akhir);

        if($pendamping!=''){
            $this->db->where('ps.nip',$pendamping);
        }

        return $this->db->get()->row()->jumlah;
    }

    public function belumLapor($tahun,$awal,$akhir,$pendamping='')
    {
        $this->db->from('m_swk s');
        $this->db->join('pendamping_swk ps','ps.idswk=s.idswk','left');
        $this->db->where('s.aktif',1);

        if($pendamping!='') {
            $this->db->where('ps.nip',$pendamping);
        }

        $sub="
        SELECT 1
        FROM t_perform p
        WHERE p.idswk=s.idswk
        AND p.tahun=".$this->db->escape($tahun)."
        AND p.bulan BETWEEN ".$this->db->escape($awal)." AND ".$this->db->escape($akhir);
        $this->db->where("NOT EXISTS($sub)",NULL,FALSE);
        return $this->db->count_all_results();
    }

    public function capaian($tahun,$awal,$akhir,$pendamping='')
    {
        $this->db->select("
            ROUND(
            IFNULL(
            SUM(LEAST(d.realisasi,d.target))
            /
            NULLIF(SUM(d.target),0)
            *100
            ,0)
            ,2) nilai
            ",FALSE);

        $this->db->from('t_perform_detail d');
        $this->db->join('t_perform p','p.idperform=d.idperform');
        $this->db->join('pendamping_swk ps','ps.idswk=p.idswk','left');

        $this->db->where('p.tahun',$tahun);
        $this->db->where('p.bulan >=',$awal);
        $this->db->where('p.bulan <=',$akhir);

        if($pendamping!=''){
            $this->db->where('ps.nip',$pendamping);
        }
        $nilai=$this->db->get()->row()->nilai;
        return min(100,$nilai);
    }

    public function rankingTerbaik($tahun,$awal,$akhir,$pendamping='')
    {
        $sql="
        SELECT
        s.idswk,
        s.nama_swk,
        ROUND(
        IFNULL(
        SUM(LEAST(d.realisasi,d.target))
        /
        NULLIF(SUM(d.target),0)
        *100
        ,0)
        ,2) nilai
        FROM t_perform p
        INNER JOIN t_perform_detail d
        ON p.idperform=d.idperform
        INNER JOIN m_swk s
        ON p.idswk=s.idswk
        LEFT JOIN pendamping_swk ps
        ON ps.idswk=s.idswk
        WHERE p.tahun=?
        AND p.bulan BETWEEN ? AND ?";

        $params=array($tahun,$awal,$akhir);

        if($pendamping!=''){
            $sql.=" AND ps.nip=?";
            $params[]=$pendamping;
        }

        $sql.=" GROUP BY s.idswk
        ORDER BY nilai DESC,nama_swk
        LIMIT 10";

        return $this->db->query($sql,$params)->result();
    }

    public function rankingTerendah($tahun,$awal,$akhir,$pendamping='')
    {
        $sql="
        SELECT
        s.idswk,
        s.nama_swk,
        ROUND(
        IFNULL(
        SUM(LEAST(d.realisasi,d.target))
        /
        NULLIF(SUM(d.target),0)
        *100
        ,0)
        ,2) nilai
        FROM t_perform p
        INNER JOIN t_perform_detail d
        ON p.idperform=d.idperform
        INNER JOIN m_swk s
        ON p.idswk=s.idswk
        LEFT JOIN pendamping_swk ps
        ON ps.idswk=s.idswk
        WHERE p.tahun=?
        AND p.bulan BETWEEN ? AND ?";

        $params=array($tahun,$awal,$akhir);

        if($pendamping!=''){
            $sql.=" AND ps.nip=?";
            $params[]=$pendamping;
        }

        $sql.=" GROUP BY s.idswk
        ORDER BY nilai ASC,nama_swk
        LIMIT 10";

        return $this->db->query($sql,$params)->result();
    }

    public function swkBelumLapor($tahun,$awal,$akhir,$pendamping='')
    {
        $this->db->select("
            s.nama_swk, s.alamat,
            IFNULL(u.nama_lengkap,'-') nama_pendamping
            ");

        $this->db->from('m_swk s');
        $this->db->join('pendamping_swk ps','ps.idswk=s.idswk','left');
        $this->db->join('m_users u','u.nik=ps.nip','left');

        $this->db->where('s.aktif',1);

        if($pendamping!=''){
            $this->db->where('ps.nip',$pendamping);
        }

        $sub="
        SELECT 1
        FROM t_perform p
        WHERE p.idswk=s.idswk
        AND p.tahun=".$this->db->escape($tahun)."
        AND p.bulan BETWEEN ".$this->db->escape($awal)." AND ".$this->db->escape($akhir);

        $this->db->where("NOT EXISTS($sub)",NULL,FALSE);

        return $this->db
        ->order_by('s.nama_swk','ASC')
        ->get()
        ->result();
    }

}
