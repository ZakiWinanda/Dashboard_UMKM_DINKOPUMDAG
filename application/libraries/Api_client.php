<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_client
{
    protected $CI;
    private $api_base_url;
    private $api_username;
    private $api_password;
    private $token = null;

    public function __construct()
    {
        $this->CI =& get_instance();

        // Load config api jika belum ter-load
        if (!$this->CI->config->item('api_base_url', 'api')) {
            $this->CI->config->load('api', TRUE);
        }

        $this->api_base_url = $this->CI->config->item('api_base_url', 'api') ?: 'https://dinkop-umkm.live-energeek.id/api';
        $this->api_username = $this->CI->config->item('api_username', 'api') ?: 'api_integration';
        $this->api_password = $this->CI->config->item('api_password', 'api') ?: 'Integration@2026!';
    }

    public function get_base_url()
    {
        return $this->api_base_url;
    }

    /**
     * Ambil Access Token dari API secara terpusat (dengan caching memory)
     */
    public function get_token()
    {
        if ($this->token) {
            return $this->token;
        }

        $payload = json_encode([
            'username' => $this->api_username,
            'password' => $this->api_password,
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->api_base_url . '/auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
        ]);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);

        if ($err || empty($response)) {
            log_message('error', 'Api_client Login Error: ' . $err);
            return false;
        }

        $result = json_decode($response, true);
        if (isset($result['data']['token']['accessToken'])) {
            $this->token = $result['data']['token']['accessToken'];
            return $this->token;
        }

        return false;
    }

    /**
     * Ambil daftar kecamatan berdasarkan NIP pendamping dari API eksternal.
     * Endpoint: GET /api/integration/kecamatan-pendamping
     *
     * @param string|null $nip      NIP pendamping (null = semua kecamatan, khusus admin/pimpinan)
     * @param int         $per_page Jumlah data per halaman (default 100, maks 500)
     * @return array  List kecamatan atau [] jika gagal
     */
    public function get_kecamatan_by_nip($nip = null, $per_page = 100)
    {
        $token = $this->get_token();
        if (!$token) {
            log_message('error', 'Api_client::get_kecamatan_by_nip — tidak berhasil mendapatkan token');
            return [];
        }

        $all      = [];
        $page     = 1;
        $lastPage = 1;

        do {
            $params = [
                'per_page' => $per_page,
                'page'     => $page,
            ];
            if (!empty($nip)) {
                $params['nip'] = $nip;
            }

            $url = $this->api_base_url . '/integration/kecamatan-pendamping?' . http_build_query($params);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            $err      = curl_error($curl);
            curl_close($curl);

            if ($err || empty($response)) {
                log_message('error', 'Api_client::get_kecamatan_by_nip cURL Error: ' . $err);
                break;
            }

            $result   = json_decode($response, true);
            $items    = $result['data']['data']       ?? [];
            $lastPage = $result['data']['pagination']['lastPage'] ?? 1;

            foreach ($items as $item) {
                $all[] = $item;
            }

            $page++;
        } while ($page <= $lastPage && count($all) < 500);

        return $all;
    }

    /**
     * Ambil data legalitas SWK dari API eksternal.
     * Endpoint: GET /api/integration/legalitas-swk
     */
    public function get_legalitas_swk($bulan, $tahun, $nip = null, $per_page = 100)
    {
        return $this->_fetch_paginated('/integration/legalitas-swk', [
            'bulan'    => (int)$bulan,
            'tahun'    => (int)$tahun,
            'nip'      => $nip,
            'per_page' => $per_page
        ]);
    }

    /**
     * Ambil data kenaikan omzet SWK dari API eksternal.
     * Endpoint: GET /api/integration/omzet-swk
     */
    public function get_omzet_swk($bulan, $tahun, $nip = null, $per_page = 100)
    {
        return $this->_fetch_paginated('/integration/omzet-swk', [
            'bulan'    => (int)$bulan,
            'tahun'    => (int)$tahun,
            'nip'      => $nip,
            'per_page' => $per_page
        ]);
    }

    /**
     * Ambil data legalitas Industri Rumahan dari API eksternal (untuk Pendamping Kecamatan).
     * Endpoint: GET /api/integration/legalitas-industri-rumahan
     */
    public function get_legalitas_industri_rumahan($bulan, $tahun, $nip = null, $per_page = 100)
    {
        return $this->_fetch_paginated('/integration/legalitas-industri-rumahan', [
            'bulan'    => (int)$bulan,
            'tahun'    => (int)$tahun,
            'nip'      => $nip,
            'per_page' => $per_page
        ]);
    }

    /**
     * Ambil data kenaikan omzet Industri Rumahan dari API eksternal (untuk Pendamping Kecamatan).
     * Endpoint: GET /api/integration/omzet-industri-rumahan
     */
    public function get_omzet_industri_rumahan($bulan, $tahun, $nip = null, $per_page = 100)
    {
        return $this->_fetch_paginated('/integration/omzet-industri-rumahan', [
            'bulan'    => (int)$bulan,
            'tahun'    => (int)$tahun,
            'nip'      => $nip,
            'per_page' => $per_page
        ]);
    }

    /**
     * Helper privat untuk request paginasi API eksternal secara terpusat
     */
    private function _fetch_paginated($endpoint, $params = [])
    {
        $token = $this->get_token();
        if (!$token) {
            log_message('error', "Api_client::_fetch_paginated ($endpoint) — gagal mendapatkan token");
            return [];
        }

        $all      = [];
        $page     = 1;
        $lastPage = 1;
        $per_page = $params['per_page'] ?? 100;

        do {
            $queryParams = array_filter($params, function($v) {
                return $v !== null && $v !== '';
            });
            $queryParams['page']     = $page;
            $queryParams['per_page'] = $per_page;

            $url = $this->api_base_url . $endpoint . '?' . http_build_query($queryParams);

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_CUSTOMREQUEST  => 'GET',
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Accept: application/json',
                ],
            ]);

            $response = curl_exec($curl);
            $err      = curl_error($curl);
            curl_close($curl);

            if ($err || empty($response)) {
                log_message('error', "Api_client::_fetch_paginated ($endpoint) cURL Error: " . $err);
                break;
            }

            $result   = json_decode($response, true);
            $items    = $result['data']['data'] ?? [];
            $lastPage = $result['data']['pagination']['lastPage'] ?? 1;

            foreach ($items as $item) {
                $all[] = $item;
            }

            $page++;
        } while ($page <= $lastPage && count($all) < 500);

        return $all;
    }
}
