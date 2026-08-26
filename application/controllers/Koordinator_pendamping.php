<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Koordinator_pendamping extends My_Controller {

	public function __construct()
	{
		parent::__construct();

		if(!$this->is_admin && !$this->is_pimpinan && !$this->is_koordinator_pendamping) redirect('dashboard');

		$this->load->model('M_swk');
		$this->load->model('M_target');
		$this->load->model('M_indikator');
		$this->load->model('M_pengguna');
		$this->load->model('M_koordinator_pendamping');
	}

	public function index()
	{
		$data['title'] = "INDIKATOR";
		$this->load->view('header', $data);
		$this->load->view('indikator');
	}

	public function capaian_harian()
	{
        $idswk = trim($this->input->get('idswk'));
		$periode    = trim($this->input->post('filter_bulan_tahun'));
		$bulan = date('m', strtotime('01-'.$periode));
		$tahun = date('Y', strtotime('01-'.$periode));
		$view = 'capaian_harian/'.$this->role;
		$data = array(
            'bulan' => $bulan,
            'tahun' => $tahun,
            'idswk' => $idswk
        );

		$data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : $this->M_koordinator_pendamping->get_by_koordinator($this->nip);
		$data['swk'] = $this->is_pimpinan || $this->is_admin ? $this->M_swk->get_all() : $this->M_swk->get_by_koordinator($this->nip);

		$data['title'] = "OMSET & KUNJUNGAN HARIAN";
		$this->load->view('header', $data);
		$this->load->view('capaian_harian/koordinator_pendamping/index');
	}

	public function entri()
	{
		// $data['pendamping'] = $this->M_pengguna->get_pendamping();
		$data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : $this->M_koordinator_pendamping->get_by_koordinator($this->nip);
		$data['swk'] = $this->is_pimpinan || $this->is_admin ? $this->M_swk->get_all() : $this->M_swk->get_by_koordinator($this->nip);
		$data['title'] = "ENTRI CAPAIAN INDIKATOR";
		$this->load->view('header', $data);
		$this->load->view('indikator/koordinator_pendamping/index');
	}

	public function laporan($param='')
	{
		if($this->input->post()) {
			if($param=='enkripsi') $this->_enkripsi();
		}
		else {
			if($param=='' || $param=='index') {
				$data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : $this->M_koordinator_pendamping->get_by_koordinator($this->nip);
				$data['swk'] = $this->is_pimpinan || $this->is_admin ? $this->M_swk->get_all() : $this->M_swk->get_by_koordinator($this->nip);

				$data['title'] = 'Laporan';
		        $this->load->view('header', $data);
				$this->load->view('laporan/koordinator_pendamping/index', $data);
		    }
		    else {
		    	$decode = $this->encryption->decode(urldecode($param));

		        if (!$decode) {
		            show_404();
		        }

		        $filter = json_decode($decode, TRUE);

		        if (!is_array($filter)) {
		            show_404();
		        }

		        $bulan_awal  = $filter['bulan_awal'];
		        $bulan_akhir = $filter['bulan_akhir'];
		        $idswk = $filter['swk'];
		        $swk = $this->M_swk->get_by_id($idswk);

		        $data = array(
		            'swk' => $swk,
		            'bulan_awal'  => $bulan_awal,
		            'bulan_akhir' => $bulan_akhir,
		            'idswk' => $idswk,
		            'laporan'     => $this->M_laporan->get_laporan(
		                $bulan_awal,
		                $bulan_akhir,
		                $idswk
		            )
		        );

				$this->load->view('laporan/pendamping/cetak', $data);
		    }
		}

	}

	// public function get_swk()
	// {
		// $nip_koordinator = $this->input->post('nip_koordinator');
		// $nip_pendamping = $this->input->post('idpendamping');

		// if(($this->is_pimpinan || $this->is_admin) && empty($nip_pendamping)) $data = $this->M_swk->get_all();
		// elseif($this->is_koordinator_pendamping && empty($nip_pendamping)) $data = $this->M_swk->get_by_koordinator($nip_koordinator);
		// else $data = $this->M_swk->get_by_pendamping($nip_pendamping);

		// echo json_encode($data);
	// }

}
