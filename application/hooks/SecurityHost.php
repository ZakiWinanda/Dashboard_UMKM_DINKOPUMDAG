<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityHost {

    public function validate_host()
    {
        $allowed_hosts = array(
            'localhost',
            '172.20.70.123'
        );
        $host = isset($_SERVER['HTTP_HOST'])
            ? strtolower(trim($_SERVER['HTTP_HOST']))
            : '';

        $host = preg_replace('/:\d+$/', '', $host);
        if (!in_array($host, $allowed_hosts)) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/plain');

            echo 'Forbidden Host';
            exit;
        }
    }
}
