<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->sess = $this->session->userdata();
	}

	public function index()
	{
		// $password_hash = password_hash('@Surabaya2026', PASSWORD_DEFAULT);
		// var_dump($password_hash);die;
		if($this->input->post()) {
			$this->_login_backoffice();
		}
		else {
			if(isset($this->sess['monev_swk'])) {
				if(count($this->sess['monev_swk'])>=3) redirect('dashboard', 'refresh');
			}
			elseif(isset($this->sess['akses']) && ($this->sess['akses']=='reject')) {
				goto form_login;
			}

			form_login:
			$data = array('captcha' => $this->create_captcha());
			$data['title'] = "Login";
			$this->load->view('login', $data);
		}
	}

	function _login_backoffice()
	{
	    if (!$this->input->post()) {
	        redirect('login','refresh');
	        return;
	    }

	    $username = trim($this->input->post('input_username', TRUE));
	    $password = trim($this->input->post('input_password', TRUE));
	    $captcha  = trim($this->input->post('captcha', TRUE));

	    if (empty($username) || empty($password) || empty($captcha)) {
	        $this->_login_error('Data login tidak lengkap!', $username);
	        return;
	    }

	    if (!hash_equals((string)$this->session->userdata('captchaword'), (string)$captcha)) {
	        $this->_login_error('Code Captcha tidak sesuai!', $username);
	        return;
	    }

	    $max_attempt = 5;
	    $lock_minutes = 1;

	    $attempt = (int)$this->session->userdata('login_attempt');
	    $block_time = (int)$this->session->userdata('login_block_time');
	    $attempt_time = (int)$this->session->userdata('login_attempt_time');

	    if ($attempt > 0 && (time() - $attempt_time) > ($lock_minutes * 60)) {
	        $this->session->unset_userdata(['login_attempt','login_block_time','login_attempt_time']);
	        $attempt = 0;
	    }

	    if ($attempt >= $max_attempt) {
	        if (!$block_time) {
	            $this->session->set_userdata('login_block_time', time());
	            $block_time = time();
	        }
	        $elapsed   = time() - $block_time;
	        $remaining = ($lock_minutes * 60) - $elapsed;
	        if ($remaining > 0) {
	            $minutes = floor($remaining / 60);
	            $seconds = $remaining % 60;

	            $this->_login_error(
	                "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit {$seconds} detik.", $username
	            );
	            return;
	        } else {
	            $this->session->unset_userdata(['login_attempt','login_block_time','login_attempt_time']);
	            $attempt = 0;
	        }
	    }

	    $user = $this->_cekuser($username, $password);
	    if (empty($user)) {
	        $attempt++;
	        $this->session->set_userdata([
	            'login_attempt'      => $attempt,
	            'login_attempt_time' => time()
	        ]);

	        if ($attempt >= $max_attempt) {
	            $this->session->set_userdata('login_block_time', time());
	        }
	        log_message('error', 'Login gagal: '.$username);
	        $this->_login_error('Username dan Password tidak sesuai!', $username);
	        return;
	    }

	    $this->session->unset_userdata(['login_attempt','login_block_time','login_attempt_time']);
	    $this->session->sess_regenerate(TRUE);
	    $this->session->set_userdata([
	        'monev_swk' => $user,
	        'login_time' => time()
	    ]);
	    redirect('dashboard','refresh');
	}

	private function _login_error($pesan, $username = '')
	{
	    $arr_flash = [
	        'status'   => 'error',
	        'alert'    => '',
	        'pesan'    => htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'),
	        'username' => $username
	    ];

	    $this->session->set_flashdata('pemberitahuan', json_encode($arr_flash));
	    redirect('login','refresh');
	}

	function _cekuser($username, $password)
	{
		$arr = array();
		$query = $this->db->query("select * from m_users where aktif=1 and nik='$username'");
		if($query->num_rows()<=0) {
			return $arr;
			exit;
		}
		$user = $query->row();
		if(!password_verify($password, $user->pass))
		{
			return $arr;
			exit;
		}

		$result = array(
			'nip'=>$user->nik,
			'nama'=>$user->nama_lengkap,
			'role'=>$user->role,
		);
		return $result;
	}

	public function create_captcha()
	{
	    $this->load->helper('captcha');
	    $vals = array(
	        'img_path'      => FCPATH.'captcha/',
	        'img_url'       => base_url('captcha/'),
	        'font_path'     => FCPATH.'system/fonts/cambriai.ttf',
	        'img_width'     => 150,
	        'img_height'    => 35,
	        'expiration'    => 7200,
	        'word_length'   => 4,
	        'pool'          => '0123456789',
	        'colors'        => array(
	            'background' => array(255,255,255),
	            'border'     => array(255,255,255),
	            'text'       => array(0,0,0),
	            'grid'       => array(255,255,0)
	        )
	    );

	    $captcha = create_captcha($vals);
	    if ($captcha === FALSE)
	    {
	        show_error('Captcha gagal dibuat.');
	    }
	    $this->session->set_userdata('captchaword', $captcha['word']);
	    return $captcha['image'];
	}
}
