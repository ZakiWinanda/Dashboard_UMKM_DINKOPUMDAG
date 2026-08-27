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
}
