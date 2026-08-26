<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_perform extends CI_Model{

    public function simpan($header, $detail, $omset)
    {
        $this->db->trans_begin();

        $perform = $this->getPerform(
            $header['idswk'],
            $header['bulan'],
            $header['tahun']
        );

        if ($perform) {
            $idperform = $perform->idperform;
        }
        else {
            $this->db->insert('t_perform', $header);
            $idperform = $header['idperform'];
        }

        if (!empty($omset)) {
            $setomset = $this->getOmset(
                $header['idswk'],
                $header['bulan'],
                $header['tahun']
            );
            if ($setomset) {
                $this->db->where('idomset', $setomset->idomset);
                $this->db->update('m_omset', array(
                    'omset'        => $omset['omset'],
                    'omset_plus_1' => $omset['omset_plus_1']
                ));
            }
            else {
                $realisasi = isset($omset['omset']) ? (float) str_replace(array('.', ','), array('', '.'), $omset['omset']) : 0;
                $dataOmset = array(
                    'idomset'		=> uuid_v4(),
                    'idswk'         => $header['idswk'],
                    'bulan'         => $header['bulan'],
                    'tahun'         => $header['tahun'],
                    'omset'         => $realisasi,
                    'omset_plus_1'  => $realisasi * 1.01
                );
                $this->db->insert('m_omset', $dataOmset);
            }
        }

        foreach ($detail as $d) {
            $d['idperform'] = $idperform;
            $this->db
            ->where('idperform', $idperform)
            ->where('idindikator', $d['idindikator']);

            if (isset($d['subindikator'])) {
                $this->db->where('subindikator', $d['subindikator']);
            }

            $cek = $this->db->get('t_perform_detail')->row();

            if ($cek) {
                unset($d['iddetail']);

                $this->db
                ->where('iddetail', $cek->iddetail)
                ->update('t_perform_detail', $d);
            }
            else {
                $this->db->insert('t_perform_detail', $d);
            }
        }

        if ($this->db->trans_status() == FALSE) {
            $this->db->trans_rollback();
            return FALSE;
        }

        $this->db->trans_commit();
        return TRUE;
    }

    public function getPerform($idswk, $bulan, $tahun)
    {
        return $this->db
        ->where('idswk', $idswk)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->get('t_perform')
        ->row();
    }

    public function getOmset($idswk, $bulan, $tahun)
    {
        return $this->db
        ->where('idswk', $idswk)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->get('m_omset')
        ->row();
    }

    public function get_header($idswk,$bulan,$tahun)
    {
        return $this->db
        ->where('idswk',$idswk)
        ->where('bulan',$bulan)
        ->where('tahun',$tahun)
        ->get('t_perform')
        ->row();
    }

    public function get_detail($idperform)
    {
        return $this->db
        ->join('m_indikator ps', 'ps.idindikator = p.idindikator', 'left')
        ->where('p.idperform',$idperform)
        ->get('t_perform_detail p')
        ->result();
    }

    public function get_target_master($tahun)
    {
        return $this->db
        ->join('m_indikator ps', 'ps.idindikator = p.idindikator', 'left')
        ->where('p.tahun', $tahun)
        ->order_by('p.idindikator')
        ->get('m_target p')
        ->result();
    }

    public function get_jumlah_stan($idswk)
    {
        return $this->db
        ->select('stan')
        ->where('p.idswk', $idswk)
        ->get('m_swk p')
        ->row();
    }

    public function get_omset_master($idswk, $tahun, $bulan)
    {
        if ($bulan == 1) {
            $bulan = 12;
            $tahun = $tahun - 1;
        }
        else {
            $bulan = $bulan - 1;
        }

        return $this->db
        ->select('omset, omset_plus_1')
        ->where('idswk', $idswk)
        ->where('tahun', $tahun)
        ->where('bulan', $bulan)
        ->get('m_omset')
        ->row();
    }
}
