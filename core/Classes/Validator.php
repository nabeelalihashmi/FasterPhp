<?php

namespace Core\Classes;

use Rakit\Validation\Validator as ValidationValidator;

class Validator {
    private static $_instance = null;

    public function __construct() {
        self::$_instance = new  ValidationValidator();
    }

    public static function instance()
    {
        if (self::$_instance == null) {
            new self();
        }

        return self::$_instance;
    }


    public static function newinstance()
    {
        return new  ValidationValidator();
    }
}