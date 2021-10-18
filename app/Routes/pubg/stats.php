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
    d('page:' , 'home');
    $sql = "";
    $bindings = [];
    if ($category != 'all') {
        $sql = 'where category = ?';
        $bindings[] = $category;
    }
    

    $stats = R::findAll('stats', $sql, $bindings);
    d('stats', $stats);
}

function proxy_get_weapon($order, $id) { 
    d('calling proxy function', "id:  $id", "order: $order");
    echo 'Calling Home Proxy';
    d('page:' , 'home proxy');
    $sql = "";
    $bindings = [];
    
    $sql = 'where id = ?';
    $bindings[] = $id;


    $stats = R::findAll('stats', $sql, $bindings);
    d('proxy stats', $stats);
}