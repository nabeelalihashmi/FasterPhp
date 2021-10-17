<?php

namespace Core\Classes;

use Evenement\EventEmitter;


class Emitter {
    private static $_instance = null;

    public function __construct() {
        self::$_instance = new EventEmitter();
    }

    public static function instance()
    {
        if (self::$_instance == null) {
            new self();
        }

        return self::$_instance;
    }

    public static function newInstance()
    {  
        return new EventEmitter();;
    }

    public static function emit($event, $arguments = []) {
        self::instance()->emit($event, $arguments);
    }
    
    public static function on($event, $listener) {
        self::instance()->on($event, $listener);
    }


}