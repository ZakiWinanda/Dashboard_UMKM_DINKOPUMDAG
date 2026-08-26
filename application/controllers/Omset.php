<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Omset extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('M_omset','omset');
    }

    public function index()
    {
        $data['swk']=$this->db
        ->order_by('nama_swk')
        ->get('m_swk')
        ->result();

        $this->load->view('omset/index',$data);
    }

    public function ajax_list()
    {
        $list=$this->omset->get_datatables();

        $data=array();
        $no=$_POST['start'];

        foreach($list as $r) {
            $no++;
            $row=array();

            $row[]=$no;
            $row[]=$r->nama_swk;
            $row[]=$r->tahun;
            $row[]=bulan($r->bulan);
            $row[]=number_format($r->omset,0,',','.');
            $row[]=number_format($r->omset_plus_1,0,',','.');

            $row[]='
            <button class="btn btn-warning btn-sm" onclick="edit_data(\''.$r->idomset.'\')"> Edit</button>
            <button class="btn btn-danger btn-sm" onclick="hapus(\''.$r->idomset.'\')"> Hapus</button>';
            $data[]=$row;
        }

        echo json_encode(array(
            "draw"=>$_POST['draw'],
            "recordsTotal"=>$this->omset->count_all(),
            "recordsFiltered"=>$this->omset->count_filtered(),
            "data"=>$data
        ));
    }
