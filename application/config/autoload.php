<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$autoload['packages']  = array();
$autoload['libraries'] = array('database', 'session', 'form_validation', 'curl', 'encryption', 'api_client');
$autoload['drivers']   = array();
$autoload['helper']    = array('url', 'tgl_indo', 'cookie', 'captcha', 'uuid');
$autoload['config']    = array('api');
$autoload['language']  = array();
$autoload['model']     = array();
