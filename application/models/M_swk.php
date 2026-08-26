<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_swk extends CI_Model
{
    protected $table = 'm_swk';
    protected $column_order = array(null, 'idswk', 'nama_swk', 'alamat', 'stan', 'aktif');
    protected $column_search = array('idswk', 'nama_swk', 'alamat');
    protected $default_order = array('nama_swk' => 'ASC');

    private $api_base_url = 'https://dinkop-umkm.live-energeek.id/api';

    private function _get_datatables_query()
    {
        $post = $this->input->post(NULL, TRUE);
        $this->db->from($this->table);
        $this->db->where('aktif', 1);

        if (!empty($post['search']['value'])) {
            $keyword = trim($post['search']['value']);
            $this->db->group_start();

            foreach ($this->column_search as $i => $column) {
                if ($i == 0) {
                    $this->db->like($column, $keyword);
                }
                else {
                    $this->db->or_like($column, $keyword);
                }
            }
            $this->db->group_end();
        }

        if (isset($post['order'][0])) {
            $column_index = (int)$post['order'][0]['column'];
            $direction    = strtolower($post['order'][0]['dir']);
            $direction = ($direction === 'desc') ? 'DESC' : 'ASC';

            if (isset($this->column_order[$column_index]) && !empty($this->column_order[$column_index])) {
                $this->db->order_by(
                    $this->column_order[$column_index],
                    $direction
                );
            }
            else {
                foreach ($this->default_order as $col => $dir) {
                    $this->db->order_by($col, $dir);
                }
            }
        }
        else {
            foreach ($this->default_order as $col => $dir) {
                $this->db->order_by($col, $dir);
            }
        }
    }

    public function get_datatables()
    {
        $post = $this->input->post(NULL, TRUE);
        $this->_get_datatables_query();

        $length = isset($post['length']) ? (int)$post['length'] : 10;
        $start  = isset($post['start'])  ? (int)$post['start']  : 0;

        if ($length > 100) {
            $length = 100;
        }

        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        return $this->db->get()->result();
    }

    public function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->db->count_all_results();
    }

    public function count_all()
    {
        return $this->db->count_all($this->table);
    }

    public function get_all()
    {
        return $this->db
            ->select('idswk,nama_swk')
            ->where('aktif', 1)
            ->order_by('nama_swk','ASC')
            ->get('m_swk')
            ->result();
    }

    public function get_by_pendamping($nip)
    {
        return $this->db
            ->select('m_swk.idswk,
                      m_swk.nama_swk,
                      m_swk.alamat,
                      m_swk.stan')
            ->from('pendamping_swk')
            ->join('m_swk','m_swk.idswk = pendamping_swk.idswk')
            ->where('pendamping_swk.nip', $nip)
            ->where('m_swk.aktif', 1)
            ->order_by('m_swk.nama_swk','ASC')
            ->get()
            ->result();
    }

    public function get_by_koordinator($nip_koordinator)
    {
        return $this->db
            ->select('
                m_swk.idswk,
                m_swk.nama_swk,
                m_swk.alamat,
                m_swk.stan
            ')
            ->from('koordinator_pendamping kp')
            ->join('pendamping_swk ps', 'ps.nip = kp.nip_pendamping')
            ->join('m_swk', 'm_swk.idswk = ps.idswk')
            ->where('kp.nip_koordinator', $nip_koordinator)
            ->where('m_swk.aktif', 1)
            ->order_by('m_swk.nama_swk', 'ASC')
            ->get()
            ->result();
    }

    public function get_by_id($idswk)
    {
        return $this->db
            ->select("
                s.*,
                GROUP_CONCAT(
                    DISTINCT u.nama_lengkap
                    ORDER BY u.nama_lengkap
                    SEPARATOR ', '
                ) AS nama_pendamping,
                GROUP_CONCAT(
                    DISTINCT u.nik
                    ORDER BY u.nama_lengkap
                    SEPARATOR ', '
                ) AS nip_pendamping
            ")
            ->from('m_swk s')
            ->join('pendamping_swk ps', 'ps.idswk = s.idswk', 'left')
            ->join('m_users u', 'u.nik = ps.nip', 'left')
            ->where('s.idswk', $idswk)
            ->where('s.aktif', 1)
            ->group_by('s.idswk')
            ->get()
            ->row();
    }
    /**
     * Otentikasi dan mengambil bearer token
     */
    public function get_api_token(){

        $curl = curl_init();
        $payload = json_encode([
            'username' => 'api_integration',
            'password' => 'Integration@2026!'
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->api_base_url . '/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err || empty($response)) {
            log_message('error', 'API Login Error: ' . $err);
            return false;
        }

        $result = json_decode($response, true);

        // Ambil accessToken sesuai struktur JSON API
        if (isset($result['data']['token']['accessToken'])) {
            return $result['data']['token']['accessToken'];
        }

        return false;
}
    /**
     * Mengambil daftar nama SWK dari API
     */
    public function get_api_swk($per_page = 100){
       $token = $this->get_api_token();
        if (!$token) {
            return [];
        }

        $url = $this->api_base_url . '/integration/swk?' . http_build_query([
            'search'             => '',
            'updated_at_start'   => '',
            'updated_at_end'     => '',
            'per_page'           => $per_page,
            'page'               => 1
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $token,
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response, true);

        $list_swk = [];
        if (isset($result['data']['data']) && is_array($result['data']['data'])) {
            foreach ($result['data']['data'] as $item) {
                $list_swk[] = [
                    'id'       => $item['id'],
                    'nama_swk' => $item['name']
                ];
            }
        }

        return $list_swk;
    }

    public function get_swk_by_user($nip, $role = 'pendamping')
    {
        // 1. Ambil seluruh data SWK dari API
        $all_swk = $this->get_api_swk();
        if (empty($all_swk)) {
            return [];
        }

        // 2. Jika Administrator / Pimpinan, tampilkan semua SWK
        if ($role == 'administrator' || $role == 'pimpinan') {
            return $all_swk;
        }
        // 3. Ambil penugasan dari tabel pendamping_swk berdasarkan NIP
        $this->db->select('idswk');
        $this->db->where('nip', $nip);
        $assigned = $this->db->get('pendamping_swk')->result_array();

        if (empty($assigned)) {
            return [];
        }

        $assigned_ids = array_column($assigned, 'idswk');

        // 4. Cocokkan ID dari API dengan data penugasan NIP
        $filtered_swk = [];
        foreach ($all_swk as $swk) {
            if (in_array($swk['id'], $assigned_ids)) {
                $filtered_swk[] = $swk;
            }
        }

        return $filtered_swk;

    }


}
