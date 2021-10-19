<?php

/**
 * Route Schema
 * 
 * This is for demo only. better choice is to use weapon.php file.
 */
$route_schema = [
    'allowed_methods' => ['get', 'post', 'delete', 'put'],
    'methods' => [
        'get' => [
            'proxies' => [
                ['?/weapon/?', 'proxy_get_weapon']
            ]
        ]
    ]
];

function get($category = 'all') {
    echo 'Calling Home';
    d('page:', 'home');
    $sql = "";
    $bindings = [];
    if ($category != 'all') {
        $sql = 'where category = ?';
        $bindings[] = $category;
    }


    $stats = R::findAll('stats', $sql, $bindings);
    d('stats', $stats);
}

