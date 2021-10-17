<?php

namespace Core\Classes;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Cache {
    private static $_instance = null;

    public function __construct() {
        self::$_instance = new FilesystemAdapter('', 0, 'storage/cache');
    }

    
    public static function instance()
    {
        if (self::$_instance == null) {
            new self();
        }

        return self::$_instance;
    }
}