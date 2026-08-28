<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Indikator extends My_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('M_swk');
        $this->load->model('M_target');
        $this->load->model('M_indikator');
        $this->load->model('M_pengguna');
	}

	public function index()
	{
		$data['title'] = "INDIKATOR";
		$this->load->view('header', $data);
		$this->load->view('indikator'); 
	}

public function capaian_harian()
{
    redirect('capaian_harian','refresh');
}

  public function entri()
  {
    $view = 'indikator/'.$this->role;

    $data['pendamping'] = $this->is_pimpinan || $this->is_admin ? $this->M_pengguna->get_pendamping() : '';
    $data['swk'] = $this->is_pimpinan || $this->is_admin  ? $this->M_swk->get_all() : $this->M_swk->get_by_pendamping($this->nip);

	$data['title'] = "ENTRI CAPAIAN INDIKATOR";
    $this->load->view('header', $data);

    if(file_exists(APPPATH.'views/'.$view.'/index.php')){
      $this->load->view($view.'/index', $data);
    }
  }

    public function simpan()
    {
        $idswk = $this->input->post('idswk');
        $bulan_tahun = $this->input->post('bulan');

    	if(empty($idswk) || empty($bulan_tahun)) {
    		echo json_encode(array(
    			'status' => FALSE,
    			'pesan'  => 'Data gagal disimpan'
    		));
    		exit;
    	}

        $bulan = date('m',strtotime("01-".$this->input->post('bulan')));
        $tahun = date('Y',strtotime("01-".$this->input->post('bulan')));

        $idperform = uuid_v4();

        $header=array(
          'idperform'=>$idperform,
          'idswk'=>$idswk,
          'bulan'=>$bulan,
          'tahun'=>$tahun,
          'created_by'=>$this->nip
        );

        $detail=array();
        $omset=array();

        $indikator=$this->input->post('indikator');
        $idindikator = $this->M_indikator->get_idindikator($indikator);

        /* start upload file */
        $nama_file = '';
        if(isset($_FILES['data_dukung']) && $_FILES['data_dukung']['name'] != '') {

            $upload = $this->upload_file('data_dukung');
            if(!$upload['status']) {
                echo json_encode(array(
                    'status'=>FALSE,
                    'pesan'=>$upload['error']
                ));
                exit;
            }

            $nama_file = $upload['file'];
        }
        /* end upload file */

        $target=$this->input->post('target');
        $realisasi=$this->input->post('realisasi');

        if(is_array($target)) {
          foreach($target as $k=>$v) {

            if($indikator=='kenaikan_omset') {
    			$vv = str_replace('.', '', $v);
    			$realisasi = str_replace('.', '', $realisasi[$k]);
    			$detail[]=array(
    				'iddetail'=>uuid_v4(),
    				'idperform'=>$idperform,
    				'idindikator'=>$idindikator,
    				'target'=>$vv,
    				'realisasi'=>$realisasi
    			);

    			$omset_plus_1 = $realisasi + ($realisasi * 1 / 100);
                $omset = array(
                    'idomset'=>uuid_v4(),
                    'idswk'=>$idswk,
                    'tahun'=>$tahun,
                    'bulan'=>$bulan,
                    'omset'=>$realisasi,
                    'omset_plus_1'=>$omset_plus_1,
                    'created_by'=>$this->nip
                );
            }
            elseif(is_array($v)) {
              foreach($v as $sub=>$t) {
                $detail[]=array(
                  'iddetail'=>uuid_v4(),
                  'idperform'=>$idperform,
                  'idindikator'=>$idindikator,
                  'subindikator'=>$sub,
                  'target'=>$t,
                  'realisasi'=>$realisasi[$k][$sub],
                  'data_dukung'=>$nama_file
                );
              }
            }
    		else {
              $detail[]=array(
                'iddetail'=>uuid_v4(),
                'idperform'=>$idperform,
                'idindikator'=>$idindikator,
                'target'=>$v,
                'realisasi'=>$realisasi[$k],
                'data_dukung'=>$nama_file
              );
            }
          }
        }
        elseif (is_array($realisasi)) {
          foreach($realisasi as $k=>$v) {
            if(is_array($v)) {
              foreach($v as $sub=>$r) {
                $detail[] = array(
                  'iddetail'      => uuid_v4(),
                  'idindikator'   => $idindikator,
                  'subindikator'  => $sub,
                  'target'        => NULL,
                  'realisasi'     => $r
                );
              }
            }
          }
        }

        if($this->input->post('keterangan')) {
          foreach($this->input->post('keterangan') as $k=>$v) {
            $detail[]=array(
              'iddetail'=>uuid_v4(),
              'idperform'=>$idperform,
              'idindikator'=>$idindikator,
              'nilai_text'=>$v,
              'data_dukung'=>$nama_file
            );
          }
        }

        if($this->input->post('status')) {
          $detail[]=array(
            'iddetail'=>uuid_v4(),
            'idperform'=>$idperform,
            'idindikator'=>$idindikator,
            'nilai_radio'=>$this->input->post('status'),
            'data_dukung'=>$nama_file
          );
        }

      	$subindikator = array('kebersihan_ruang_makan', 'kebersihan_tenan', 'kebersihan_toilet', 'kebersihan_area_parkir', 'kebersihan_produk_makanan', 'sampah_terpilah', 'lahan_parkir', 'juru_parkir');
        if(in_array($indikator, $subindikator)) {
          $nilai = $this->input->post($indikator);
          if($nilai != '') {
            $detail[] = array(
              'iddetail'      => uuid_v4(),
              'idperform'     => $idperform,
              'idindikator'   => $idindikator,
              'nilai_radio'   => $nilai
            );
          }
        }

    	$this->load->model('M_perform');
        if($this->M_perform->simpan($header,$detail,$omset)) {
            echo json_encode(array(
                'status' => TRUE,
                'pesan'  => 'Data berhasil disimpan'
            ));
        }
        else {
            echo json_encode(array(
                'status' => FALSE,
                'pesan'  => 'Data gagal disimpan'
            ));
        }

        exit;
    }

    private function upload_file($field, $folder = 'data_dukung')
    {
        if (empty($_FILES[$field]['name'])) {
            return array(
                'status' => TRUE,
                'file'   => ''
            );
        }

        $path = FCPATH . 'assets/uploads/' . $folder . '/';
        if (!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }

        $nama_asli = $_FILES[$field]['name'];
        $ext  = pathinfo($nama_asli, PATHINFO_EXTENSION);
        $name = pathinfo($nama_asli, PATHINFO_FILENAME);
        $filename = $name . '_' . date('YmdHis') . '_' . substr(md5(uniqid()),0,6) . '.' . strtolower($ext);

        $config['upload_path']   = $path;
        $config['allowed_types'] = 'jpg|jpeg';
        $config['max_size']      = 1024;
        $config['file_name']     = $filename;
        $config['overwrite']     = FALSE;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload($field)) {
            return array(
                'status' => FALSE,
                'error'  => strip_tags($this->upload->display_errors())
            );
        }

        $upload = $this->upload->data();
        return array(
            'status' => TRUE,
            'file'   => $upload['file_name']
        );
    }

    public function filter()
    {
      if($this->role=='pimpinan') $this->_filter_petugas();
      else $this->_filter_petugas();
    }

    function _filter_petugas()
    {
        $idswk   = trim($this->input->post('idswk'));
        $periode = trim($this->input->post('bulan'));

        if($idswk=='' || $periode=='')
        {
            echo json_encode(array(
                'status'=>FALSE,
                'pesan'=>'Parameter tidak lengkap.'
            ));
            return;
        }

        $bulan=date('m',strtotime('01-'.$periode));
        $tahun=date('Y',strtotime('01-'.$periode));

        $this->load->model('M_swk');
        $this->load->model('M_perform');

        $swk=$this->M_swk->get_by_id($idswk);

        if(!$swk)
        {
            echo json_encode(array(
                'status'=>FALSE,
                'pesan'=>'SWK tidak ditemukan.'
            ));
            return;
        }

        $detail=array();
        $status_indikator=array();

        $master=$this->M_perform->get_target_master($tahun);
    	$keterisian_stan = $this->M_perform->get_jumlah_stan($idswk)->stan;
        $omset = $this->M_perform->get_omset_master($idswk, $tahun, $bulan);
    	$sub_target = array('tingkat_keterisian_stan', 'kelengkapan_administrasi');

        foreach($master as $m)
        {
            $key=trim((string)$m->subindikator);

    		if($m->kode=='kenaikan_omset' && !is_object($omset)) {
    			echo json_encode(array(
    				'status'=>FALSE,
    				'pesan'=>'Omset bulan lalu tidak ditemukan'
    			));
    			return;
    		}

    		if(in_array($m->kode, $sub_target)) $target = $keterisian_stan;
    		else $target = $m->kode=='kenaikan_omset' ? number_format($omset->omset_plus_1, 0, ',', '.') : number_format($m->target, 0, ',', '.');
    		$detail[$m->kode][$key]=array(
                'subindikator'=>$key,
                'target'=>$target,
                'realisasi'=>'',
                'nilai_text'=>'',
                'nilai_radio'=>''
            );
            $status_indikator[$m->kode]=FALSE;
        }

        $perform=$this->M_perform->get_header($idswk, $bulan, $tahun);
        if($perform) {
            $rows=$this->M_perform->get_detail($perform->idperform);

            foreach($rows as $d) {
                $key=trim((string)$d->subindikator);

                if(!isset($detail[$d->kode])) {
                    $detail[$d->kode]=array();
                }

                if(!isset($detail[$d->kode][$key])) {
                    $detail[$d->kode][$key]=array(
                        'subindikator'=>$key,
                        'target'=>$d->target,
                        'realisasi'=>'',
                        'nilai_text'=>'',
                        'nilai_radio'=>''
                    );
                }

                if($d->target!==NULL) {
                    $detail[$d->kode][$key]['target']=$d->target;
                }

                $detail[$d->kode][$key]['realisasi']=$d->realisasi;
                $detail[$d->kode][$key]['nilai_text']=$d->nilai_text;
                $detail[$d->kode][$key]['nilai_radio']=$d->nilai_radio;
                $detail[$d->kode][$key]['data_dukung']=$d->data_dukung;

                if(
                    $d->realisasi!==NULL ||
                    $d->nilai_text!='' ||
                    $d->nilai_radio!=''
                ){
                    $status_indikator[$d->kode]=TRUE;
                }
            }
        }

        foreach($detail as $id=>$row)
        {
            $detail[$id]=array_values($row);
        }

        echo json_encode(array(

            'status'=>TRUE,

            'data'=>array(
                'nama_swk'=>$swk->nama_swk,
                'alamat'=>$swk->alamat,
                'stan'=>$swk->stan,
                'bulan_tahun'=>strtoupper(
                    strftime(
                        '%B %Y',
                        strtotime($tahun.'-'.$bulan.'-01')
                    )
                )
            ),

            'detail'=>$detail,

            'status_indikator'=>$status_indikator

        ));
    }

    function _filter_pimpinan()
    {
        var_dump($this->input->post());die;
        $idpendamping = trim($this->input->post('idpendamping'));
        $idswk      = trim($this->input->post('idswk'));
        $periode    = trim($this->input->post('filter_bulan_tahun'));

        if ($periode == '')
        {
            echo json_encode(array(
                'status' => FALSE,
                'pesan'  => 'Periode belum dipilih.'
            ));
            return;
        }

        $bulan = date('m', strtotime('01-'.$periode));
        $tahun = date('Y', strtotime('01-'.$periode));

        $this->load->model('M_swk');
        $this->load->model('M_perform');

        /*
        ===================================================
        Ambil daftar SWK sesuai filter
        ===================================================
        */

        if ($idswk != '')
        {
            // hanya satu SWK
            $list_swk = array($this->M_swk->get_by_id($idswk));
        }
        else
        {
            // semua SWK atau berdasarkan pendamping
            $list_swk = $this->M_swk->get_filter($idpendamping);
        }

        $hasil = array();

        foreach ($list_swk as $swk)
        {
            if (!$swk) {
                continue;
            }

            $detail = array();
            $status_indikator = array();

            /*
            ====================================
            MASTER TARGET
            ====================================
            */

            $master = $this->M_perform->get_target_master($tahun);

            foreach ($master as $m)
            {
                $key = trim((string)$m->subindikator);

                $detail[$m->idindikator][$key] = array(
                    'subindikator' => $key,
                    'target'       => $m->target,
                    'realisasi'    => '',
                    'nilai_text'   => '',
                    'nilai_radio'  => ''
                );

                $status_indikator[$m->idindikator] = FALSE;
            }

            /*
            ====================================
            DATA PERFORM
            ====================================
            */

            $perform = $this->M_perform->get_header(
                $swk->idswk,
                $bulan,
                $tahun
            );

            if ($perform)
            {
                $rows = $this->M_perform->get_detail($perform->idperform);

                foreach ($rows as $d)
                {
                    $key = trim((string)$d->subindikator);

                    if (!isset($detail[$d->idindikator][$key]))
                    {
                        $detail[$d->idindikator][$key] = array(
                            'subindikator' => $key,
                            'target'       => $d->target,
                            'realisasi'    => '',
                            'nilai_text'   => '',
                            'nilai_radio'  => ''
                        );
                    }

                    if ($d->target !== NULL)
                        $detail[$d->idindikator][$key]['target'] = $d->target;

                    $detail[$d->idindikator][$key]['realisasi']   = $d->realisasi;
                    $detail[$d->idindikator][$key]['nilai_text']  = $d->nilai_text;
                    $detail[$d->idindikator][$key]['nilai_radio'] = $d->nilai_radio;

                    if (
                        $d->realisasi !== NULL ||
                        $d->nilai_text != '' ||
                        $d->nilai_radio != ''
                    ) {
                        $status_indikator[$d->idindikator] = TRUE;
                    }
                }
            }

            foreach ($detail as $id => $row)
            {
                $detail[$id] = array_values($row);
            }

            $hasil[] = array(
                'idswk' => $swk->idswk,
                'nama_swk' => $swk->nama_swk,
                'alamat' => $swk->alamat,
                'stan' => $swk->stan,
                'bulan_tahun' => strtoupper(strftime(
                    '%B %Y',
                    strtotime($tahun.'-'.$bulan.'-01')
                )),
                'detail' => $detail,
                'status_indikator' => $status_indikator
            );
        }

        echo json_encode(array(
            'status' => TRUE,
            'data'   => $hasil
        ));
    }

	// public function get_swk()
	// {
		// $nip_koordinator = $this->input->post('nip_koordinator');
		// $nip_pendamping = $this->input->post('idpendamping');

		// if($this->is_pimpinan && empty($nip_pendamping)) $data = $this->M_swk->get_all();
		// elseif($this->is_koordinator_pendamping && empty($nip_pendamping)) $data = $this->M_swk->get_by_koordinator($nip_koordinator);
		// else $data = $this->M_swk->get_by_pendamping($nip_pendamping);

		// echo json_encode($data);
	// }
}
