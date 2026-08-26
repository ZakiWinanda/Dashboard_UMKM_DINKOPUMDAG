<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SecurityHeaders {

    public function set_headers()
    {
        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
        header_remove("X-Powered-By");
        header("Server: SecureServer");

        $csp = "default-src 'self'; "
             . "frame-ancestors 'self'; "
             . "img-src 'self' data:; "
             . "script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
             . "style-src 'self' 'unsafe-inline';";

        header("Content-Security-Policy: " . $csp);
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }
}
