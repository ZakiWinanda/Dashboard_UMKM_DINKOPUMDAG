<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_laporan extends CI_Model{

    public function get_laporan($bulan_awal, $bulan_akhir, $idswk)
    {
        $tgl_awal  = date('Y-m-01', strtotime('01-'.$bulan_awal));
        $tgl_akhir = date('Y-m-t', strtotime('01-'.$bulan_akhir));

        $this->db->select("
            p.idperform,
            p.idswk,
            p.bulan,
            p.tahun,

            d.idindikator,
            d.subindikator,
            d.target,
            d.realisasi,
            d.nilai_text,
            d.nilai_radio,

            a.kode
        ");

        $this->db->from('t_perform p');
        $this->db->join('t_perform_detail d', 'd.idperform = p.idperform', 'left' );
        $this->db->join('m_indikator a', 'a.idindikator = d.idindikator', 'left' );

        if(!empty($idswk)) {
            $this->db->where('p.idswk', $idswk);
        }

        $this->db->where("
            STR_TO_DATE(
                CONCAT(p.tahun,'-',LPAD(p.bulan,2,'0'),'-01'),
                '%Y-%m-%d'
            ) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."'
        ", NULL, FALSE);

        $this->db->order_by('p.tahun','ASC');
        $this->db->order_by('p.bulan','ASC');
        $this->db->order_by('d.idindikator','ASC');
        $this->db->order_by('d.subindikator','ASC');

        return $this->db->get()->result();
    }
}
