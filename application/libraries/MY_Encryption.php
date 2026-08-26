<?php
class MY_Encryption extends CI_Encryption
{
    public function encode($string)
    {
        $encrypted = parent::encrypt($string);
        return strtr(base64_encode($encrypted), ['+' => '.', '=' => '-', '/' => '~']);
    }

    public function decode($string)
    {
        $string = base64_decode(strtr($string, ['.' => '+', '-' => '=', '~' => '/']));
        return parent::decrypt($string);
    }
}

?>