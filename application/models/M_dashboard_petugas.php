<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_dashboard_petugas extends CI_Model
{
public function totalSwk($nip = '')
{
    $this->db->from('m_swk s');
    $this->db->where('s.aktif', 1);

    if (!empty($nip)) {
        $this->db->join('pendamping_swk ps', 'ps.idswk = s.idswk');
        $this->db->where('ps.nip', $nip);
    }

    $this->db->select('COUNT(DISTINCT s.idswk) AS total', FALSE);

    return (int)$this->db->get()->row()->total;
}

public function lastUpdate($nip = '', $tahun, $bulan)
{
    $this->db->select('MAX(p.created_at) AS last_update', FALSE);
    $this->db->from('t_perform p');

    if (!empty($nip)) {
        $this->db->join('pendamping_swk ps', 'ps.idswk = p.idswk');
        $this->db->where('ps.nip', $nip);
    }

    $this->db->where('p.tahun', $tahun);
    $this->db->where('p.bulan', $bulan);

    $row = $this->db->get()->row();

    return (!empty($row->last_update)) ? $row->last_update : NULL;
}

    public function sudahLapor($nip,$tahun,$bulan)
    {
        $this->db
            ->select('COUNT(DISTINCT p.idswk) jumlah')
            ->from('t_perform p')
            ->join('pendamping_swk ps','ps.idswk=p.idswk')
            ->join('m_swk s','s.idswk=p.idswk')
            ->where('s.aktif',1)
            ->where('p.tahun',$tahun)
            ->where('p.bulan',$bulan);

        if (!empty($nip)) {
            $this->db->where('ps.nip',$nip);
        }

        $row=$this->db->get()->row();
        return ($row)?(int)$row->jumlah:0;
    }

public function belumLapor($nip = '', $tahun, $bulan)
{
    $this->db->from('m_swk s');

    if (!empty($nip)) {
        $this->db->join('pendamping_swk ps', 'ps.idswk = s.idswk');
        $this->db->where('ps.nip', $nip);
    }

    $this->db->where('s.aktif', 1);

    $this->db->where("
        NOT EXISTS (
            SELECT 1
            FROM t_perform p
            WHERE p.idswk = s.idswk
            AND p.tahun = ".$this->db->escape($tahun)."
            AND p.bulan = ".$this->db->escape($bulan)."
        )
    ", NULL, FALSE);

    $this->db->select('COUNT(DISTINCT s.idswk) AS jumlah', FALSE);

    $row = $this->db->get()->row();

    return $row ? (int)$row->jumlah : 0;
}

    public function progress($nip, $tahun, $bulan)
    {
        $list = $this->daftarSwk($nip, $tahun, $bulan);
        if (empty($list)) {
            return 0;
        }
        $total = 0;
        foreach ($list as $row) {
            $total += (float)$row->persentase;
        }
        return round($total / count($list), 2);
    }

	public function daftarSwk($nip = '', $tahun, $bulan)
	{
		if (!empty($nip)) {
			$subPendamping = "
				(
					SELECT
						ps.idswk,
						GROUP_CONCAT(DISTINCT u.nama_lengkap ORDER BY u.nama_lengkap SEPARATOR ', ') AS nama_pendamping
					FROM pendamping_swk ps
					LEFT JOIN m_users u ON u.nik = ps.nip
					WHERE ps.nip = ".$this->db->escape($nip)."
					GROUP BY ps.idswk
				) pendamping
			";
		} else {
			$subPendamping = "
				(
					SELECT
						ps.idswk,
						GROUP_CONCAT(DISTINCT u.nama_lengkap ORDER BY u.nama_lengkap SEPARATOR ', ') AS nama_pendamping
					FROM pendamping_swk ps
					LEFT JOIN m_users u ON u.nik = ps.nip
					GROUP BY ps.idswk
				) pendamping
			";
		}

		$subPerform = "
			(
				SELECT
					idswk,
					MAX(idperform) AS idperform
				FROM t_perform
				WHERE tahun = ".$this->db->escape($tahun)."
				  AND bulan = ".$this->db->escape($bulan)."
				GROUP BY idswk
			) p
		";

		$this->db->select("
			s.idswk,
			s.nama_swk,
			s.alamat,
			s.stan,
			p.idperform,
			IFNULL(pendamping.nama_pendamping,'-') AS nama_pendamping,
			IF(p.idperform IS NULL,0,1) AS status
		", FALSE);

		$this->db->from('m_swk s');
		$this->db->join($subPendamping, 'pendamping.idswk = s.idswk', 'left', FALSE);
		$this->db->join($subPerform, 'p.idswk = s.idswk', 'left', FALSE);
		$this->db->where('s.aktif', 1);

		if (!empty($nip)) {
			$this->db->where('pendamping.idswk IS NOT NULL', NULL, FALSE);
		}
		$this->db->order_by('s.nama_swk', 'ASC');
		$list = $this->db->get()->result();
		foreach ($list as &$row) {
			$row->persentase = 0;
			if (!empty($row->idperform)) {
				$row->persentase = $this->getPersentaseSwk($row->idperform);
			}
		}
		return $list;
	}

    private function hitungPersentase($row)
    {
        $nilai = '';

        if ($row->nilai_text !== NULL && $row->nilai_text != '') {
            $nilai = $row->nilai_text;
        } elseif ($row->nilai_radio !== NULL && $row->nilai_radio != '') {
            $nilai = ucfirst($row->nilai_radio);
        } else {
            $nilai = $row->realisasi;
        }

        switch ($row->kode) {

            case 'tingkat_keterisian_stan':
            case 'kenaikan_omset':
            case 'kelengkapan_administrasi':

                if ($row->target > 0) {
                    return min(round(($nilai / $row->target) * 100),100);
                }
                return 0;

            case 'kebersihan_ruang_makan':
            case 'kebersihan_area_parkir':
            case 'kebersihan_tenan':
            case 'kebersihan_toilet':
            case 'kebersihan_produk_makanan':
            case 'review_online':

                switch ($nilai) {
                    case 'Baik': return 100;
                    case 'Cukup': return 75;
                    case 'Buruk': return 50;
                    default: return 0;
                }

                return 0;

            case 'sampah_terpilah':
    			return ($nilai=='Sudah') ? 100 : 0;
            case 'lahan_parkir':
            case 'juru_parkir':

                return ($nilai=='Ada') ? 100 : 0;

            case 'promosi':
            case 'pemberian_ide_praktis':
            case 'rapat_evaluasi':
            case 'frekuensi_kunjungan':

                return !empty($nilai) ? 100 : 0;

            default:
                return 0;
        }
    }

    public function getPersentaseSwk($idperform)
    {
        $detail = $this->db
            ->select('
                i.*,
                p.idperform,
                p.target,
                p.realisasi,
                p.nilai_radio,
                p.nilai_text
            ')
            ->from('m_indikator i')
            ->join(
                't_perform_detail p',
                'p.idindikator=i.idindikator
                 AND p.idperform='.$this->db->escape($idperform),
                'left'
            )
            ->where('i.aktif',1)
            ->order_by('i.urut','ASC')
            ->get()
            ->result();

        if (!$detail) {
            return 0;
        }

        $group = array();

        foreach($detail as $row){

            // lewati parent
            if(empty($row->parent_id) && in_array($row->kode,array(
                'tingkat_kebersihan',
                'kelengkapan_administrasi'
            ))){
                continue;
            }

            $persen = $this->hitungPersentase($row);

            switch($row->kode){

                case 'kebersihan_ruang_makan':
                case 'kebersihan_tenan':
                case 'kebersihan_toilet':
                case 'kebersihan_area_parkir':
                case 'kebersihan_produk_makanan':
                case 'sampah_terpilah':
                case 'lahan_parkir':
                case 'juru_parkir':

                    $group['tingkat_kebersihan'][] = $persen;
                    break;

                case 'nib':
                case 'sk_penempatan':
                case 'satu_data':

                    $group['kelengkapan_administrasi'][] = $persen;
                    break;

                default:

                    $group[$row->kode][] = $persen;
            }
        }

        $total = 0;
        $jumlah = 0;
        foreach($group as $item){
            $rata = array_sum($item) / count($item);
            $total += $rata;
            $jumlah++;
        }
        return $jumlah>0 ? round($total/$jumlah,2) : 0;
    }

    public function monitoringTerakhir($nip = '')
    {

        $where_nip = '';
        $params = array();

        if (!empty($nip)) {
            $where_nip = " AND ps.nip = ? ";
            $params[] = $nip;
        }

        $sql = "
        SELECT
            p.idperform,
            p.created_at,
            p.bulan,
            p.tahun,
            s.nama_swk
        FROM t_perform p
        INNER JOIN m_swk s
            ON s.idswk = p.idswk
        WHERE EXISTS (
            SELECT 1
            FROM pendamping_swk ps
            WHERE ps.idswk = p.idswk
            {$where_nip}
        )
        ORDER BY p.created_at DESC
        LIMIT 5";

        return $this->db->query($sql, $params)->result();
    }

    public function getNilaiKinerjaPendamping($nip,$tahun,$bulan)
    {
        $this->db
            ->select('p.idperform')
            ->from('pendamping_swk ps')
            ->join(
                't_perform p',
                'p.idswk = ps.idswk
                AND p.tahun = '.$this->db->escape($tahun).'
                AND p.bulan = '.$this->db->escape($bulan),
                'left'
            );

        if (!empty($nip)) {
            $this->db->where('ps.nip', $nip);
        }

        $swk = $this->db->get()->result();

        if(empty($swk)){
            return 0;
        }

        $total = 0;
        $jumlah = 0;
        foreach($swk as $row){
            if(empty($row->idperform)){
    			$total += 0;
            }
    		else {
    			$total += $this->getNilaiKinerjaSwk($row->idperform);
            }
    		$jumlah++;
        }
        if($jumlah==0){
            return 0;
        }
        return round($total/$jumlah,2);
    }

    public function getNilaiKinerjaSwk($idperform)
    {
        $detail = $this->db
            ->select("
                i.idindikator,
                i.kode,
                i.tipe,
                i.parent_id,
                i.bobot,

                MAX(d.target) target,
                SUM(d.realisasi) realisasi,
                MAX(d.nilai_radio) nilai_radio,
                MAX(d.nilai_text) nilai_text,
                COUNT(d.iddetail) jumlah
            ")
            ->from('m_indikator i')
            ->join(
                't_perform_detail d',
                'd.idindikator=i.idindikator
                AND d.idperform='.$this->db->escape($idperform),
                'left'
            )
            ->where('i.aktif',1)
            ->group_by('i.idindikator')
            ->order_by('i.urut')
            ->get()
            ->result();

        if(empty($detail)){
            return 0;
        }

        $master = $this->db
            ->select('kode,bobot')
            ->where('parent_id IS NULL',null,false)
            ->get('m_indikator')
            ->result();

        $bobotParent=array();
        foreach($master as $m){
            $bobotParent[$m->kode]=$m->bobot;
        }

        $jumlahChild=array();
        foreach($detail as $row){
            if(!empty($row->parent_id)){
                if(!isset($jumlahChild[$row->parent_id])){
                    $jumlahChild[$row->parent_id]=0;
                }
                $jumlahChild[$row->parent_id]++;
            }
        }
    	
        $nilai=0;
        foreach($detail as $row){
    		
    		// ambil persentase berdasarkan isian indikator atau bisa juga di tetapkan 100
            $persen=$this->hitungPersentase($row);
    		
            if(empty($row->parent_id)){
                $bobot=$row->bobot;
            }
    		else{
                $bobot=$bobotParent[$row->parent_id]/$jumlahChild[$row->parent_id];
            }
            $nilai+=($persen*$bobot)/100;
        }
        return round($nilai,2);
    }

    public function swkBelumLapor($nip = '', $tahun, $bulan)
    {
        $this->db->select("
            s.idswk,
            s.nama_swk,
            s.alamat,
            IFNULL(u.nama_lengkap,'-') AS nama_pendamping
        ");

        $this->db->from('m_swk s');
        $this->db->join('pendamping_swk ps', 'ps.idswk = s.idswk', 'left');
        $this->db->join('m_users u', 'u.nik = ps.nip', 'left');

        $this->db->where('s.aktif', 1);

        if (!empty($nip)) {
            $this->db->where('ps.nip', $nip);
        }

        $this->db->where("
            NOT EXISTS (
                SELECT 1
                FROM t_perform p
                WHERE p.idswk = s.idswk
                AND p.tahun = ".$this->db->escape($tahun)."
                AND p.bulan = ".$this->db->escape($bulan)."
            )
        ", NULL, FALSE);

        return $this->db
            ->order_by('s.nama_swk', 'ASC')
            ->get()
            ->result();
    }

}