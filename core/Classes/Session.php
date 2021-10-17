<?php

namespace Core\Classes;

use Symfony\Component\HttpFoundation\Session\Session as SessionSession;

class Session {
    private static $_instance = null;

    public function __construct() {
        self::$_instance = new SessionSession();
    }

    public static function start() {
        self::instance()->start();
        
    }
    
    public static function instance()
    {
        if (self::$_instance == null) {
            new self();
        }

        return self::$_instance;
    }
}