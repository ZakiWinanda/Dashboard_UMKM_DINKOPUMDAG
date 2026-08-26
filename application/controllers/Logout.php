<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Logout extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->sess = $this->session->userdata();
	}

	public function index()
	{
		// var_dump($this->sess);die;
		// $arr_sess['logout'] = $this->sess['monev_tbm']['username'];
		// $this->session->set_userdata($arr_sess);
		// $this->session->unset_userdata('movev_tbm');
		$this->session->sess_destroy();
		redirect('login');
	}
}