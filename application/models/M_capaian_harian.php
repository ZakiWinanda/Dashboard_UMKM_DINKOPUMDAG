<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_capaian_harian extends CI_Model
{
    public function getOmsetHarian($idswk, $bulan, $tahun)
    {
        $idswk = trim((string)$idswk);

        $qDirect = $this->db
            ->select('tanggal, omset')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, FALSE)
            ->where('YEAR(tanggal)', (int)$tahun, FALSE)
            ->get('t_omset_harian')
            ->result();

        $qUnit = $this->db
            ->select('tanggal, SUM(omset) AS omset', FALSE)
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, FALSE)
            ->where('YEAR(tanggal)', (int)$tahun, FALSE)
            ->group_by('tanggal')
            ->get('t_omset_unit_usaha')
            ->result();

        $map = [];
        foreach ($qDirect as $r) {
            $map[$r->tanggal] = (float)$r->omset;
        }
        foreach ($qUnit as $r) {
            $map[$r->tanggal] = (float)$r->omset;
        }

        ksort($map);
        $res = [];
        foreach ($map as $tgl => $val) {
            $res[] = (object)['tanggal' => $tgl, 'omset' => $val];
        }
        return $res;
    }

    public function getOmsetByTanggal($idswk, $tanggal)
    {
        return $this->db
            ->where('idswk', $idswk)
            ->where('tanggal', $tanggal)
            ->get('t_omset_harian')
            ->row();
    }

    public function saveOmset($data)
    {
        $cek = $this->db
            ->where('idswk', $data['idswk'])
            ->where('tanggal', $data['tanggal'])
            ->get('t_omset_harian')
            ->row();

        if ($cek) {
            $update = array(
                'omset'      => $data['omset'],
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->db
                ->where('id', $cek->id)
                ->update('t_omset_harian', $update);
            return $cek->id;
        } else {
            $this->db->insert('t_omset_harian', $data);
            return $this->db->insert_id();
        }
    }

    public function getKunjunganHarian($idswk, $bulan, $tahun)
    {
        $idswk = trim((string)$idswk);

        $qDirect = $this->db
            ->select('tanggal, jumlah')
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, FALSE)
            ->where('YEAR(tanggal)', (int)$tahun, FALSE)
            ->get('t_kunjungan_harian')
            ->result();

        $qUnit = $this->db
            ->select('tanggal, SUM(jumlah) AS jumlah', FALSE)
            ->where('idswk', $idswk)
            ->where('MONTH(tanggal)', (int)$bulan, FALSE)
            ->where('YEAR(tanggal)', (int)$tahun, FALSE)
            ->group_by('tanggal')
            ->get('t_kunjungan_unit_usaha')
            ->result();

        $map = [];
        foreach ($qDirect as $r) {
            $map[$r->tanggal] = (int)$r->jumlah;
        }
        foreach ($qUnit as $r) {
            $map[$r->tanggal] = (int)$r->jumlah;
        }

        ksort($map);
        $res = [];
        foreach ($map as $tgl => $val) {
            $res[] = (object)['tanggal' => $tgl, 'jumlah' => $val];
        }
        return $res;
    }

    public function getKunjunganByTanggal($idswk, $tanggal)
    {
        return $this->db
            ->where('idswk', $idswk)
            ->where('tanggal', $tanggal)
            ->get('t_kunjungan_harian')
            ->row();
    }

    public function saveKunjungan($data)
    {
        $cek = $this->db
            ->where('idswk', $data['idswk'])
            ->where('tanggal', $data['tanggal'])
            ->get('t_kunjungan_harian')
            ->row();

        if ($cek) {
            $update = array(
                'jumlah'     => $data['jumlah'],
                'updated_at' => date('Y-m-d H:i:s')
            );

            $this->db
                ->where('id', $cek->id)
                ->update('t_kunjungan_harian', $update);
            return $cek->id;
        } else {
            $this->db->insert('t_kunjungan_harian', $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Hitung total omset bulanan dari t_omset_harian
     */
    public function totalOmsetBulanan($idswk, $bulan, $tahun)
    {
        $this->db->select_sum('omset');
        $this->db->where('idswk', $idswk);
        $this->db->where('MONTH(tanggal)', (int)$bulan, FALSE);
        $this->db->where('YEAR(tanggal)', (int)$tahun, FALSE);
        $q1 = (float)($this->db->get('t_omset_harian')->row()->omset ?? 0);

        $this->db->select_sum('omset');
        $this->db->where('idswk', $idswk);
        $this->db->where('MONTH(tanggal)', (int)$bulan, FALSE);
        $this->db->where('YEAR(tanggal)', (int)$tahun, FALSE);
        $q2 = (float)($this->db->get('t_omset_unit_usaha')->row()->omset ?? 0);

        return max($q1, $q2);
    }

    public function totalKunjunganBulanan($idswk, $bulan, $tahun)
    {
        $this->db->select_sum('jumlah');
        $this->db->where('idswk', $idswk);
        $this->db->where('MONTH(tanggal)', (int)$bulan, FALSE);
        $this->db->where('YEAR(tanggal)', (int)$tahun, FALSE);
        $q1 = (int)($this->db->get('t_kunjungan_harian')->row()->jumlah ?? 0);

        $this->db->select_sum('jumlah');
        $this->db->where('idswk', $idswk);
        $this->db->where('MONTH(tanggal)', (int)$bulan, FALSE);
        $this->db->where('YEAR(tanggal)', (int)$tahun, FALSE);
        $q2 = (int)($this->db->get('t_kunjungan_unit_usaha')->row()->jumlah ?? 0);

        return max($q1, $q2);
    }

    public function omsetArray($idswk, $bulan, $tahun)
    {
        $hasil = array();
        $rows = $this->getOmsetHarian($idswk, $bulan, $tahun);
        foreach ($rows as $r) {
            $hasil[(int)date('j', strtotime($r->tanggal))] = $r->omset;
        }
        return $hasil;
    }

    public function kunjunganArray($idswk, $bulan, $tahun)
    {
        $hasil = array();
        $rows = $this->getKunjunganHarian($idswk, $bulan, $tahun);
        foreach ($rows as $r) {
            $hasil[(int)date('j', strtotime($r->tanggal))] = $r->jumlah;
        }
        return $hasil;
    }

    public function getDataBulanan($idswk, $bulan, $tahun)
    {
        return array(
            'omset'           => $this->omsetArray($idswk, $bulan, $tahun),
            'kunjungan'       => $this->kunjunganArray($idswk, $bulan, $tahun),
            'total_omset'     => $this->totalOmsetBulanan($idswk, $bulan, $tahun),
            'total_kunjungan' => $this->totalKunjunganBulanan($idswk, $bulan, $tahun),
            'jumlah_hari'     => cal_days_in_month(CAL_GREGORIAN, (int)$bulan, (int)$tahun)
        );
    }

    public function updatePerformOmset($idswk, $tanggal)
    {
        $bulan = (int)date('m', strtotime($tanggal));
        $tahun = (int)date('Y', strtotime($tanggal));

        $idindikator = 'f9fe5494-7522-11f1-92f4-0040b87d9637';

        $realisasi = $this->totalOmsetBulanan($idswk, $bulan, $tahun);
        $master = $this->db
            ->where('idswk', $idswk)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get('m_omset')
            ->row();

        $target = 0;
        if ($master) {
            $target = $master->omset_plus_1;
        }

        $perform = $this->db
            ->where('idswk', $idswk)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get('t_perform')
            ->row();

        if ($perform) {
            $idperform = $perform->idperform;
        } else {
            $idperform = uuid_v4();
            $this->db->insert('t_perform', array(
                'idperform'  => $idperform,
                'idswk'      => $idswk,
                'bulan'      => $bulan,
                'tahun'      => $tahun,
                'created_by' => $this->session->userdata('nip')
            ));
        }

        $detail = $this->db
            ->where('idperform', $idperform)
            ->where('idindikator', $idindikator)
            ->get('t_perform_detail')
            ->row();

        if ($detail) {
            $update = array(
                'realisasi' => $realisasi
            );

            if (empty($detail->target)) {
                $update['target'] = $target;
            }

            $this->db
                ->where('iddetail', $detail->iddetail)
                ->update('t_perform_detail', $update);
        } else {
            $this->db->insert('t_perform_detail', array(
                'iddetail'    => uuid_v4(),
                'idperform'   => $idperform,
                'idindikator' => $idindikator,
                'target'      => $target,
                'realisasi'   => $realisasi
            ));
        }
    }

    public function updatePerformKunjungan($idswk, $tanggal)
    {
        $bulan = (int)date('n', strtotime($tanggal));
        $tahun = (int)date('Y', strtotime($tanggal));

        $indikator = $this->db
            ->select('idindikator')
            ->where('kode', 'frekuensi_kunjungan')
            ->get('m_indikator')
            ->row();

        if (!$indikator) {
            return FALSE;
        }

        $idindikator = $indikator->idindikator;
        $realisasi = $this->totalKunjunganBulanan($idswk, $bulan, $tahun);
        $perform = $this->db
            ->where('idswk', $idswk)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get('t_perform')
            ->row();

        if ($perform) {
            $idperform = $perform->idperform;
        } else {
            $idperform = uuid_v4();
            $this->db->insert('t_perform', array(
                'idperform'  => $idperform,
                'idswk'      => $idswk,
                'bulan'      => $bulan,
                'tahun'      => $tahun,
                'created_by' => $this->session->userdata('nip')
            ));
        }

        $detail = $this->db
            ->where('idperform', $idperform)
            ->where('idindikator', $idindikator)
            ->get('t_perform_detail')
            ->row();

        if ($detail) {
            $this->db
                ->where('iddetail', $detail->iddetail)
                ->update('t_perform_detail', array(
                    'nilai_text' => $realisasi
                ));
        } else {
            $this->db->insert('t_perform_detail', array(
                'iddetail'    => uuid_v4(),
                'idperform'   => $idperform,
                'idindikator' => $idindikator,
                'nilai_text'  => $realisasi
            ));
        }
        return TRUE;
    }
}