<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_master extends My_Controller {

    public function __construct()
    {
        parent::__construct();

        if(!$this->is_admin) redirect('dashboard');

        $this->load->model('M_swk');
        $this->load->model('M_pendamping');
        $this->load->model('M_master_target');
        $this->load->model('M_indikator','indikator');
        $this->load->model('M_target','target');
        $this->load->model('M_koordinator_pendamping');
        $this->load->model('M_omset','omset');
// $this->load->model('M_indikator');
    }

    public function index()
    {
        $data['title'] = "Data Master";
        $this->load->view('header', $data);
        $this->load->view('dashboard_petugas');
    }

    /* START SWK */
    public function swk()
    {
        if($this->input->post()) {
            $param = $this->uri->segment(3);
            if($param=='ajax_list') {
                $this->_swk_ajax_list();
            }
        }
        else {
            $data['title'] = "MASTER SWK";
            $this->load->view('header', $data);
            $this->load->view('data_master/swk');
        }
    }

    public function swk_update()
    {
        $id=$this->input->post('idswk',TRUE);
        $data=array(
            'nama_swk'=>$this->input->post('nama_swk',TRUE),
            'alamat'=>$this->input->post('alamat',TRUE),
            'stan'=>(int)$this->input->post('stan')
        );

        $this->db->where('idswk',$id);
        $this->db->update('m_swk',$data);
        echo json_encode(array(
            'status'=>TRUE,
            'message'=>'Data berhasil diubah.'
        ));
    }

    public function swk_simpan()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $data = array(
            'idswk'    => uuid_v4(),
            'nama_swk' => trim($this->input->post('nama_swk', TRUE)),
            'alamat'   => trim($this->input->post('alamat', TRUE)),
            'stan'     => (int)$this->input->post('stan')
        );

        $this->db->insert('m_swk', $data);

        echo json_encode(array(
            'status' => TRUE
        ));
    }

    public function swk_hapus()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $idswk = trim($this->input->post('idswk', TRUE));

        if (empty($idswk)) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'ID SWK tidak valid.'
            ));
            return;
        }

        $this->db->where('idswk', $idswk);

        if ($this->db->update('m_swk', array('aktif' => 0))) {
            echo json_encode(array(
                'status'  => TRUE,
                'message' => 'Data berhasil dinonaktifkan.'
            ));
        }
        else {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Gagal menonaktifkan data.'
            ));
        }
    }

    public function _swk_ajax_list()
    {
        $list = $this->M_swk->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = is_numeric($start) ? (int)$start : 0;
        $data = array();

        foreach ($list as $row)
        {
            $no++;
            $idswk    = html_escape($row->idswk);
            $nama_swk = html_escape($row->nama_swk);
            $alamat   = html_escape($row->alamat);
            $stan     = (int)$row->stan;
            $data[] = array(
                'no'         => $no,
                'idswk'      => $idswk,
                'nama_swk'   => $nama_swk,
                'alamat'     => $alamat,
                'stan'       => $stan,
                'aksi'       => '
                <button type="button" class="btn btn-success btn-flat btn-sm mb-1" onclick="edit(\''.$idswk.'\')"><i class="fa fa-edit"></i> Edit</button>
                <button type="button" class="btn btn-warning btn-flat btn-sm mb-1" onclick="nonaktifkan(\''.$idswk.'\', \''.$nama_swk.'\')"><i class="fa fa-ban"></i> Nonaktifkan</button>
                '
            );
        }

        $output = array(
            'draw'            => is_numeric($draw) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->M_swk->count_all(),
            'recordsFiltered' => (int)$this->M_swk->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }

    public function swk_get($id)
    {
        echo json_encode(
            $this->db
            ->where('idswk',$id)
            ->get('m_swk')
            ->row()
        );
    }
    /* END SWK */

    /* START PENDAMPING */
    public function koordinator_pendamping()
    {
        $param = $this->uri->segment(3);
        if ($this->input->is_ajax_request() && $param == 'ajax_list') {
            $this->_koordinator_pendamping_ajax_list();
            return;
        }

        $data['title'] = "MASTER KOORDINATOR PENDAMPING";
        $this->load->view('header', $data);
        $this->load->view('data_master/koordinator_pendamping');
    }

    public function _koordinator_pendamping_ajax_list()
    {
        $list = $this->M_koordinator_pendamping->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = is_numeric($start) ? (int)$start : 0;
        $data = array();

        foreach ($list as $row) {
            $no++;
            $nip_koordinator   = html_escape($row->nip_koordinator);
            $nama_koordinator  = html_escape($row->nama_koordinator);
            $jumlah_pendamping = (int)$row->jumlah_pendamping;
            $pendamping = '-';

            if (!empty($row->nama_pendamping)) {
                $items = explode('||', $row->nama_pendamping);
                $html  = array();

                foreach ($items as $i => $nama) {
                    $html[] = ($i + 1) . '. ' . html_escape(trim($nama));
                }

                $pendamping = implode('<br>', $html);
            }

            $data[] = array(
                'no'                 => $no,
                'nip_koordinator'    => $nip_koordinator,
                'nama_koordinator'   => $nama_koordinator,
                'jumlah_pendamping'  => $jumlah_pendamping,
                'nama_pendamping'    => $pendamping,
                'aksi'               => '
                <button type="button" class="btn btn-success btn-flat btn-sm mb-1" onclick="edit(\''.$nip_koordinator.'\')"> <i class="fa fa-edit"></i> Edit</button>
                <button type="button" class="btn btn-danger btn-flat btn-sm mb-1" onclick="hapus(\''.$nip_koordinator.'\', \''.$nama_koordinator.'\')"><i class="fa fa-trash"></i> Hapus</button>
                '
            );
        }

        $output = array(
            'draw'            => is_numeric($draw) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->M_koordinator_pendamping->count_all(),
            'recordsFiltered' => (int)$this->M_koordinator_pendamping->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }

    public function koordinator_pendamping_get($nip = '')
    {
        if (empty($nip)) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Koordinator tidak ditemukan.'
            ));
            return;
        }

        $koordinator = $this->db
        ->select('nip_koordinator')
        ->from('koordinator_pendamping')
        ->where('nip_koordinator', $nip)
        ->limit(1)
        ->get()
        ->row();

        if (!$koordinator) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Data tidak ditemukan.'
            ));
            return;
        }

        $pendamping = $this->db
        ->select('nip_pendamping')
        ->from('koordinator_pendamping')
        ->where('nip_koordinator', $nip)
        ->order_by('nip_pendamping', 'ASC')
        ->get()
        ->result();

        $nip_pendamping = array();

        foreach ($pendamping as $p) {
            $nip_pendamping[] = $p->nip_pendamping;
        }

        echo json_encode(array(
            'status'           => TRUE,
            'nip_koordinator'  => $koordinator->nip_koordinator,
            'nip_pendamping'   => $nip_pendamping
        ));
    }

    public function tambah_koordinator_pendamping()
    {
        $nip_koordinator = $this->input->post('nip_koordinator');
        $pendamping      = $this->input->post('nip_pendamping');

        if (empty($nip_koordinator) || empty($pendamping)) {
            echo json_encode([
                'status'  => FALSE,
                'message' => 'Data belum lengkap.'
            ]);
            return;
        }

        $this->db->trans_begin();

        foreach ($pendamping as $nip) {
            $cek = $this->db
            ->where('nip_koordinator',$nip_koordinator)
            ->where('nip_pendamping',$nip)
            ->count_all_results('koordinator_pendamping');

            if($cek == 0){
                $this->db->insert('koordinator_pendamping',[
                    'nip_koordinator'=>$nip_koordinator,
                    'nip_pendamping'=>$nip
                ]);
            }
        }

        if($this->db->trans_status()){
            $this->db->trans_commit();

            echo json_encode([
                'status'=>TRUE,
                'message'=>'Data berhasil disimpan.'
            ]);
        }
        else{
            $this->db->trans_rollback();

            echo json_encode([
                'status'=>FALSE,
                'message'=>'Data gagal disimpan.'
            ]);
        }
    }

    public function edit_koordinator_pendamping()
    {
        $nip_koordinator = $this->input->post('nip');
        $pendamping      = $this->input->post('nip_pendamping');

        if(empty($nip_koordinator)){
            echo json_encode([
                'status'=>FALSE,
                'message'=>'Koordinator tidak ditemukan.'
            ]);
            return;
        }

        $this->db->trans_begin();
        $this->db->where('nip_koordinator',$nip_koordinator);
        $this->db->delete('koordinator_pendamping');

        if(is_array($pendamping)){
            foreach($pendamping as $nip){
                $this->db->insert('koordinator_pendamping',[
                    'nip_koordinator'=>$nip_koordinator,
                    'nip_pendamping'=>$nip
                ]);

            }
        }

        if($this->db->trans_status()){
            $this->db->trans_commit();

            echo json_encode([
                'status'=>TRUE,
                'message'=>'Data berhasil diperbarui.'
            ]);
        }
        else{
            $this->db->trans_rollback();
            echo json_encode([
                'status'=>FALSE,
                'message'=>'Data gagal diperbarui.'
            ]);
        }
    }

    public function hapus_koordinator_pendamping()
    {
        $nip = $this->input->post('nip');
        $this->db->where('nip_koordinator',$nip);
        $hapus = $this->db->delete('koordinator_pendamping');
        echo json_encode([
            'status'=>$hapus,
            'message'=>$hapus ? 'Data berhasil dihapus.' : 'Data gagal dihapus.'
        ]);
    }
    /* END PENGAWAS*/


    /* START PENDAMPING */
    public function pendamping()
    {
        $param = $this->uri->segment(3);
        if ($this->input->is_ajax_request() && $param == 'ajax_list') {
            $this->_pendamping_ajax_list();
            return;
        }

        $data['title'] = "MASTER PENDAMPING";
        $this->load->view('header', $data);
        $this->load->view('data_master/pendamping');
    }

    public function _pendamping_ajax_list()
    {
        $list = $this->M_pendamping->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = (is_numeric($start)) ? (int)$start : 0;
        $data = array();
        foreach ($list as $row) {
            $no++;
            $nik              = html_escape($row->nik);
            $nama             = html_escape($row->nama_lengkap);
            $no_tlp           = html_escape($row->no_tlp);
            $jumlah_swk       = (int)$row->jumlah_swk;
            $daftar_swk       = !empty($row->daftar_swk) ? htmlspecialchars_decode($row->daftar_swk) : '';
            $daftar_kecamatan = !empty($row->daftar_kecamatan) ? htmlspecialchars_decode($row->daftar_kecamatan) : '';

            // Jika NIP terdeteksi memiliki SWK -> Pendamping SWK (kolom menampilkan daftar SWK)
            // Jika NIP TIDAK terdeteksi memiliki SWK -> Pendamping Kecamatan (kolom menampilkan daftar Kecamatan)
            if ($jumlah_swk > 0) {
                $jenis_badge = '<span class="badge badge-info"><i class="fa fa-store"></i> Pendamping SWK</span>';
                $wilayah     = !empty($daftar_swk) ? $daftar_swk : '<span class="text-muted">Belum ada SWK</span>';
                $tipe        = 'swk';
            } else {
                $jenis_badge = '<span class="badge badge-success"><i class="fa fa-map-marker-alt"></i> Pendamping Kecamatan</span>';
                $wilayah     = !empty($daftar_kecamatan) ? $daftar_kecamatan : '<span class="text-muted">Belum ada Kecamatan</span>';
                $tipe        = 'kecamatan';
            }

            $data[] = array(
                'no'               => $no,
                'nip'              => $nik,
                'nama'             => $nama,
                'no_tlp'           => $no_tlp,
                'jenis_pendamping' => $jenis_badge,
                'tipe'             => $tipe,
                'penugasan'        => $wilayah,
                'aksi'             => '
                <button type="button" class="btn btn-success btn-flat btn-sm mb-1" onclick="edit(\''.$nik.'\')"> <i class="fa fa-edit"></i> Edit</button>
                <button type="button" class="btn btn-danger btn-flat btn-sm mb-1" onclick="hapus(\''.$nik.'\', \''.$nama.'\')"><i class="fa fa-trash"></i> Hapus</button>
                '
            );
        }

        $output = array(
            'draw'            => (is_numeric($draw)) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->M_pendamping->count_all(),
            'recordsFiltered' => (int)$this->M_pendamping->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }

    public function pendamping_update()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $mode      = $this->input->post('mode');
        $nip       = trim($this->input->post('nip', TRUE));
        $idswk     = $this->input->post('idswk');
        $kecamatan = $this->input->post('kecamatan');

        if($mode=='edit') $this->_edit_pendamping($nip,$idswk,$kecamatan);
        elseif($mode=='tambah') $this->_tambah_pendamping($nip,$idswk,$kecamatan);
    }

    public function _tambah_pendamping($nip_param = '', $idswk_param = null, $kecamatan_param = null)
    {
        $nip       = $this->input->post('pilih_nip') ?: $nip_param;
        $idswk     = $this->input->post('idswk') ?: $idswk_param;
        $kecamatan = $this->input->post('kecamatan') ?: $kecamatan_param;

        if (empty($nip)) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Pendamping harus dipilih.'
            ));
            return;
        }

        if (empty($idswk) && empty($kecamatan)) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Minimal pilih satu SWK atau Kecamatan.'
            ));
            return;
        }

        $this->db->trans_begin();
        if (!empty($idswk) && is_array($idswk)) {
            foreach ($idswk as $id) {
                $this->db->insert('pendamping_swk', array(
                    'nip'   => $nip,
                    'idswk' => $id
                ));
            }
        }

        if (!empty($kecamatan) && is_array($kecamatan)) {
            foreach ($kecamatan as $k) {
                $this->db->insert('pendamping_kecamatan', array(
                    'nip'            => $nip,
                    'nama_kecamatan' => $k
                ));
            }
        }

        if ($this->db->trans_status() === TRUE) {
            $this->db->trans_commit();

            echo json_encode(array(
                'status'  => TRUE,
                'message' => 'Data berhasil disimpan.'
            ));
        }
        else {
            $this->db->trans_rollback();

            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Data gagal disimpan.'
            ));
        }
    }

    function _edit_pendamping($nip, $idswk = null, $kecamatan = null)
    {
        if (empty($nip)) {
            echo json_encode(array(
                'status'=>FALSE,
                'message'=>'Pendamping tidak ditemukan.'
            ));
            return;
        }

        $this->db->trans_begin();
        $this->db->where('nip',$nip);
        $this->db->delete('pendamping_swk');
        if(is_array($idswk)) {
            foreach($idswk as $id) {
                $this->db->insert('pendamping_swk',array(
                    'nip'=>$nip,
                    'idswk'=>$id
                ));
            }
        }

        $this->db->where('nip',$nip);
        $this->db->delete('pendamping_kecamatan');
        if(is_array($kecamatan)) {
            foreach($kecamatan as $k) {
                $this->db->insert('pendamping_kecamatan',array(
                    'nip'=>$nip,
                    'nama_kecamatan'=>$k
                ));
            }
        }

        if($this->db->trans_status()) {
            $this->db->trans_commit();
            echo json_encode(array(
                'status'=>TRUE,
                'message'=>'Data berhasil diperbarui.'
            ));
        }
        else {
            $this->db->trans_rollback();
            echo json_encode(array(
                'status'=>FALSE,
                'message'=>'Data gagal diperbarui.'
            ));
        }
    }

    public function pendamping_get($nip = '')
    {
        if(empty($nip))
        {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'NIP tidak ditemukan.'
            ));
            return;
        }

        $rows = $this->db
        ->select('idswk')
        ->from('pendamping_swk')
        ->where('nip', $nip)
        ->get()
        ->result();

        $idswk = array();
        foreach($rows as $r)
        {
            $idswk[] = $r->idswk;
        }

        $rows_kec = $this->db
        ->select('nama_kecamatan')
        ->from('pendamping_kecamatan')
        ->where('nip', $nip)
        ->get()
        ->result();

        $kecamatan = array();
        foreach($rows_kec as $rk)
        {
            $kecamatan[] = $rk->nama_kecamatan;
        }

        echo json_encode(array(
            'status'    => TRUE,
            'nip'       => $nip,
            'idswk'     => $idswk,
            'kecamatan' => $kecamatan
        ));
    }

    public function pendamping_hapus()
    {
        if(!$this->input->is_ajax_request()) {
            show_404();
        }

        $nip = $this->input->post('nip', TRUE);
        if(empty($nip)) {
            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'NIP tidak ditemukan.'
            ));
            return;
        }

        $this->db->trans_begin();
        $this->db->where('nip', $nip);
        $this->db->delete('pendamping_swk');

        $this->db->where('nip', $nip);
        $this->db->delete('pendamping_kecamatan');

        if($this->db->trans_status()) {
            $this->db->trans_commit();

            echo json_encode(array(
                'status'  => TRUE,
                'message' => 'Data penugasan pendamping berhasil dihapus.'
            ));
        }
        else {
            $this->db->trans_rollback();

            echo json_encode(array(
                'status'  => FALSE,
                'message' => 'Pendamping gagal dihapus.'
            ));
        }
    }
    /* END PENDAMPING */

    /* START PENGGUNA */
    public function pengguna()
    {
        if($this->input->post()) {
            $param = $this->uri->segment(3);
            if($param == 'ajax_list') {
                $this->_pengguna_ajax_list();
            }
        }
        else {
            $data['title'] = 'MASTER PENGGUNA';
            $this->load->view('header',$data);
            $this->load->view('data_master/pengguna');
        }
    }

    public function _pengguna_ajax_list()
    {
        $this->load->model('M_pengguna');
        $list = $this->M_pengguna->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = is_numeric($start) ? (int)$start : 0;
        $data = array();

        foreach ($list as $row) {
            $no++;
            $nik   = html_escape($row->nik);
            $nama  = html_escape($row->nama_lengkap);
            $role  = ucfirst(html_escape($row->role));
            $telp  = html_escape($row->no_tlp);
            $aktif = (int)$row->aktif;

            $status = ($aktif === 1) ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Non Aktif</span>';

            $aksi = '
            <button type="button" class="btn btn-success btn-flat btn-sm mb-1" onclick="edit(\''.$nik.'\')"><i class="fa fa-edit"></i> Edit</button>
            <button type="button" class="btn btn-warning btn-flat btn-sm mb-1" onclick="nonaktifkan(\''.$nik.'\', \''.$nama.'\')"><i class="fa fa-ban"></i> Nonaktifkan</button>
            ';

            $data[] = array(
                'no'     => $no,
                'nik'    => $nik,
                'nama'   => $nama,
                'role'   => $role,
                'telp'   => $telp,
                'status' => $status,
                'aksi'   => $aksi
            );
        }

        $output = array(
            'draw'            => is_numeric($draw) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->M_pengguna->count_all(),
            'recordsFiltered' => (int)$this->M_pengguna->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }

    public function pengguna_get($nik)
    {
        $row = $this->db
        ->where('nik',$nik)
        ->get('m_users')
        ->row();

        if(!$row) {
            echo json_encode(array(
                'status'=>FALSE
            ));
            return;
        }

        echo json_encode(array(
            'status'=>TRUE,
            'data'=>$row
        ));
    }

    public function pengguna_simpan()
    {
        $mode=$this->input->post('mode');
        $data=array(
            'nama_lengkap'=>$this->input->post('nama_lengkap',TRUE),
            'role'=>$this->input->post('role',TRUE),
            'no_tlp'=>$this->input->post('no_tlp',TRUE),
            'aktif'=>1
        );

        if($mode=='tambah') {
            $data['nik']=$this->input->post('nik',TRUE);
            $data['password']=password_hash($this->input->post('password'),PASSWORD_DEFAULT);
            $this->db->insert('m_users',$data);
        }
        else {
            if($this->input->post('password')!='') {
                $data['pass']=password_hash($this->input->post('password'),PASSWORD_DEFAULT);
            }

            $this->db->where('nik',$this->input->post('nik'));
            $this->db->update('m_users',$data);
        }

        echo json_encode(array(
            'status'=>TRUE
        ));
    }

    public function pengguna_hapus()
    {
        $nik=$this->input->post('nik');

        $this->db
        ->where('nik',$nik)
        ->update('m_users',array(
            'aktif'=>0
        ));

        echo json_encode(array(
            'status'=>TRUE
        ));
    }

    public function target($idindikator = '')
    {
        if(empty($idindikator)) {
            redirect('data_master/indikator');
        }

        $indikator = $this->indikator->get_by_id($idindikator);
        if(!$indikator) {
            show_404();
        }

        $data['title'] = 'TARGET INDIKATOR';
        $data['indikator'] = $indikator;
        $this->load->view('header',$data);
        $this->load->view('data_master/target',$data);
    }

    public function get_target($idindikator)
    {
        $idindikator = trim($idindikator);
        $result = $this->target->get_all($idindikator);
        $data = array();
        $no   = 1;

        foreach ($result as $r) {
            $idtarget      = html_escape($r->idtarget);
            $tahun         = (int)$r->tahun;
            $subindikator  = html_escape($r->subindikator);
            $target        = html_escape($r->target);
            $urut          = (int)$r->urut;

            $aksi = '
            <button type="button" class="btn btn-warning btn-sm mb-1" onclick="edit(\''.$idtarget.'\')"> Edit</button>
            <button type="button" class="btn btn-danger btn-sm mb-1" onclick="hapus(\''.$idtarget.'\')"> Hapus</button>
            ';

            $data[] = array(
                'no'            => $no++,
                'tahun'         => $tahun,
                'subindikator'  => $subindikator,
                'target'        => $target,
                'urut'          => $urut,
                'aksi'          => $aksi
            );
        }

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode(array('data' => $data)));
    }

    public function get_target_by_id($id)
    {
        echo json_encode(
            $this->target->get_by_id($id)
        );
    }

    public function simpan_target()
    {
        $idtarget = $this->input->post('idtarget');
        $data = array(
            'tahun'=>$this->input->post('tahun'),
            'idindikator'=>$this->input->post('idindikator'),
            'subindikator'=>$this->input->post('subindikator'),
            'target'=>$this->input->post('target'),
            'urut'=>$this->input->post('urut'),
            'created_by'=>$this->session->userdata('username')
        );

        if(empty($idtarget)){
            $data['idtarget']=uuid_v4();
            $this->target->insert($data);
        }
        else{
            $this->target->update($idtarget,$data);
        }

        echo json_encode(array(
            'status'=>true
        ));
    }

    public function hapus_target($id)
    {
        $this->target->delete($id);
        echo json_encode(array(
            'status'=>true
        ));

    }

    public function indikator()
    {
        $data['title'] = "MASTER INDIKATOR";

        $this->load->view('header',$data);
        $this->load->view('data_master/indikator',$data);
    }

    public function ajax_indikator()
    {
        $list = $this->indikator->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = is_numeric($start) ? (int)$start : 0;
        $data = array();

        foreach ($list as $row) {
            $no++;
            $idindikator = html_escape($row->idindikator);
            $kode        = html_escape($row->kode);
            $nama        = html_escape($row->nama);
            $tipe        = strtoupper(html_escape($row->tipe));
            $urut        = (int)$row->urut;
            $target      = (int)$row->jumlah_target;

            $status = ((int)$row->aktif === 1) ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>';

            $aksi = '
            <button type="button" class="btn btn-warning btn-flat btn-xs mb-1" onclick="edit(\''.$idindikator.'\')"><i class="fa fa-edit"></i> Edit</button>
            <button type="button" class="btn btn-info btn-flat btn-xs mb-1" onclick="target(\''.$idindikator.'\')"><i class="fa fa-bullseye"></i> Target</button>
            <button type="button" class="btn btn-danger btn-flat btn-xs mb-1" onclick="hapus(\''.$idindikator.'\', \''.$nama.'\')"><i class="fa fa-trash"></i> Hapus</button>
            ';

            $data[] = array(
                'no'      => $no,
                'kode'    => $kode,
                'nama'    => $nama,
                'tipe'    => $tipe,
                'urut'    => $urut,
                'status'  => $status,
                'target'  => $target,
                'aksi'    => $aksi
            );
        }

        $output = array(
            'draw'            => is_numeric($draw) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->indikator->count_all(),
            'recordsFiltered' => (int)$this->indikator->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }

    public function get_indikator($id='')
    {
        $row = $this->indikator->get_by_id($id);
        if($row) {
            echo json_encode($row);
        }
        else{
            echo json_encode(array());
        }
    }

    public function simpan_indikator()
    {
        $id = $this->input->post('idindikator');
        $data=array(
            'kode'=>$this->input->post('kode',TRUE),
            'nama'=>$this->input->post('nama',TRUE),
            'tipe'=>$this->input->post('tipe'),
            'urut'=>$this->input->post('urut'),
            'aktif'=>$this->input->post('aktif')
        );

        if(empty($id)){
            if($this->indikator->cek_kode($data['kode'])>0){
                echo json_encode(array(
                    'status'=>false,
                    'pesan'=>'Kode indikator sudah digunakan.'
                ));
                return;
            }

            $data['idindikator']=uuid_v4();
            $this->indikator->insert($data);
        }
        else {
            if($this->indikator->cek_kode($data['kode'],$id)>0) {
                echo json_encode(array(
                    'status'=>false,
                    'pesan'=>'Kode indikator sudah digunakan.'
                ));
                return;
            }
            $this->indikator->update($id,$data);
        }

        echo json_encode(array(
            'status'=>true
        ));

    }

    public function hapus_indikator()
    {
        $id = $this->input->post('idindikator');
        $cek = $this->target->jumlah_target($id);
        if($cek>0) {
            echo json_encode(array(
                'status'=>false,
                'pesan'=>'Indikator sudah memiliki target.'
            ));
            return;
        }

        $this->indikator->delete($id);
        echo json_encode(array(
            'status'=>true
        ));
    }

    /* START OMSET */
    public function Omset()
    {
        if($this->input->post()) {
            $param = $this->uri->segment(3);
            if($param=='ajax_list') $this->_omset_ajax_list();
            elseif($param=='simpan') $this->_omset_simpan();
            elseif($param=='hapus') $this->_omset_hapus();
            else show_404();
        }
        else {
            $data['swk'] = $this->M_swk->get_all();
            $data['title'] = "OMSET";
            $this->load->view('header', $data);
            $this->load->view('data_master/omset');
        }
    }

    public function _omset_simpan()
    {
        $id=$this->input->post('idomset');
        $omset=str_replace('.','',$this->input->post('omset'));
        $data=array(
            'idswk'=>$this->input->post('idswk'),
            'tahun'=>$this->input->post('tahun'),
            'bulan'=>$this->input->post('bulan'),
            'omset'=>$omset,
            'omset_plus_1'=>$omset*1.01,
            'updated_at'=>date('Y-m-d H:i:s'),
            'updated_by'=>$this->nip
        );

        if(empty($id)) {
            $data['idomset']=uuid_v4();
            $data['created_by']=$this->nip;
            $this->omset->insert($data);
        }
        else {
            $this->omset->update($id,$data);
        }
        echo json_encode(array('status'=>TRUE));
    }

    public function omset_edit($id)
    {
        echo json_encode($this->omset->getById($id));
    }

    public function omset_hapus($id)
    {
        $this->omset->delete($id);
        echo json_encode(array('status'=>TRUE));
    }

    public function _omset_ajax_list()
    {
        $list = $this->omset->get_datatables();
        $start = $this->input->post('start', TRUE);
        $draw  = $this->input->post('draw', TRUE);
        $no = is_numeric($start) ? (int)$start : 0;
        $data = array();
        foreach ($list as $r) {
            $no++;
            $idomset  = html_escape($r->idomset);
            $nama_swk = html_escape($r->nama_swk);
            $tahun    = (int)$r->tahun;
            $bulan    = (int)$r->bulan;
            $omset    = (float)$r->omset;
            $omset1   = (float)$r->omset_plus_1;

            $row = array();
            $row[] = $no;
            $row[] = $nama_swk;
            $row[] = $tahun;
            $row[] = bulan($bulan);
            $row[] = number_format($omset, 0, ',', '.');
            $row[] = number_format($omset1, 0, ',', '.');
            $row[] = '
            <button type="button" class="btn btn-flat btn-success btn-sm mb-1" onclick="edit_data(\''.$idomset.'\')"><i class="fa fa-edit"></i> Edit</button>
            <button type="button" class="btn btn-flat     btn-danger btn-sm mb-1" onclick="hapus(\''.$idomset.'\')"><i class="fa fa-trash"></i> Hapus</button>
            ';
            $data[] = $row;
        }

        $output = array(
            'draw'            => is_numeric($draw) ? (int)$draw : 0,
            'recordsTotal'    => (int)$this->omset->count_all(),
            'recordsFiltered' => (int)$this->omset->count_filtered(),
            'data'            => $data
        );

        return $this->output
        ->set_content_type('application/json', 'utf-8')
        ->set_output(json_encode($output));
    }
    /* END OMSET */

}
