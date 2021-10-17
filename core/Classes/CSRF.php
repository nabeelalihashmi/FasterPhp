<?php

namespace Core\Classes;

class CSRF
{
    public static function inputCSRF($name = '_token', $key = 'csrf_token')
    {
        return "<input type='hidden' name='$name' value='" . self::generateCSRFToken($key) . "'>";
    }

    public static function verifyCSRFToken($token, $key = 'csrf_token')
    {
        $sessionToken = Session::instance()->get($key);
        Session::instance()->clear($key);
        return $sessionToken == $token;
    }

    public static function verifyCSRFTokenX($token, $key = 'csrf_token')
    {
        $sessionToken = Session::instance()->get($key);
        return $sessionToken == $token;
    }


    public static function generateCSRFToken($key = 'csrf_token')
    {
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        Session::instance()->set($key,  $token);
        return $token;
    }
}
