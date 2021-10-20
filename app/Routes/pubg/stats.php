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
                // Position matters a lot
                ['?/weapon/?', 'proxy_get_weapon', 'pubg/stats/weapon.php'],
                ['?/not-weapon/?', 'proxy_get_weapon', 'pubg/stats/not_weapon.php'],
            ]
        ]
    ]
];

function get($category = 'all') {
    header('Content-Type: application/json');
    $sql = "";
    $bindings = [];
    if ($category != 'all') {
        $sql = 'where category = ?';
        $bindings[] = $category;
    }


    $stats = R::findAll('stats', $sql, $bindings);

    return json_encode(['data' => R::exportAll($stats)]);
}


