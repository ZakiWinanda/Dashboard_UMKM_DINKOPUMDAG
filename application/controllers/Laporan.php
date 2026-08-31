<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends My_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_swk');
        $this->load->model('M_laporan');
        $this->load->model('M_pengguna');
    }


	function _remap($param) {
		$this->index($param);
	}

	public function index($param)
	{
		$view = 'laporan/'.$this->role;
		if($this->input->post()) {
			if($param=='enkripsi') $this->_enkripsi();
		}
		else {
			if($param=='' || $param=='index') {
				$data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : '';

				if (!empty($this->is_pendamping_kecamatan)) {
					$this->load->model('M_kecamatan');
					$raw_kec = $this->M_kecamatan->get_kecamatan_by_user($this->nip, $this->role);
					$data['swk'] = array_map(function($k) {
						$nama = is_object($k) ? $k->nama_kecamatan : ($k['nama_kecamatan'] ?? $k);
						return (object)[
							'idswk'    => $nama,
							'nama_swk' => $nama
						];
					}, $raw_kec);
					$data['title'] = 'LAPORAN EVALUASI KECAMATAN';
				} else {
					$data['swk'] = $this->is_pimpinan || $this->is_admin ? $this->M_swk->get_all() : $this->M_swk->get_by_pendamping($this->nip);
					$data['title'] = 'LAPORAN EVALUASI SWK';
				}
		        $this->load->view('header', $data);
		        if(file_exists(APPPATH.'views/'.$view.'/index.php')){
					$this->load->view($view.'/index', $data);
				}
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

    public function _enkripsi()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $param = array(
            'bulan_awal'  => $this->input->post('bulan_awal', TRUE),
            'bulan_akhir'  => $this->input->post('bulan_akhir', TRUE),
            'swk' => $this->input->post('swk', TRUE)
        );

        echo json_encode(array(
            'status' => TRUE,
            'param'  => urlencode(
                $this->encryption->encode(
                    json_encode($param)
                )
            )
        ));
    }
}
