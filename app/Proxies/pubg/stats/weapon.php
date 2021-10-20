<?php

function proxy_get_weapon($order, $id) {
    // d('calling proxy function', "id:  $id", "order: $order");
    echo 'Calling Stats Proxy';
    // d('page:' , 'stats proxy');
    // $sql = "";
    // $bindings = [];

    // $sql = 'where id = ?';
    // $bindings[] = $id;


    // $stats = R::findAll('stats', $sql, $bindings);
    // d('proxy stats', $stats);
}

function before_proxy_get_weapon($params) { 
    // d('proxy_before_middeware', $params);

    echo 'Before middleware called';

    return true;
}