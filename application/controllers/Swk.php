<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Swk extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_swk');
    }


   

    public function get_swk()
    {
        $nip_koordinator = $this->input->post('nip_koordinator');
        $nip_pendamping = $this->input->post('idpendamping');

        if(($this->is_pimpinan || $this->is_admin) && empty($nip_pendamping)) $data = $this->M_swk->get_all();
        elseif($this->is_koordinator_pendamping && empty($nip_pendamping)) $data = $this->M_swk->get_by_koordinator($nip_koordinator);
        else $data = $this->M_swk->get_by_pendamping($nip_pendamping);

        echo json_encode($data);
    }

}
