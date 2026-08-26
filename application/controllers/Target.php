<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Target extends My_Controller {

	public function __construct()
	{
		parent::__construct();
		var_dump('asdfasdf');die;
		$this->load->model('M_swk');
		$this->load->model('M_target');
	}

	public function index()
	{
		$data['title']="MASTER TARGET";

		$this->load->view('header',$data);
		$this->load->view('data_master/target');
	}
}