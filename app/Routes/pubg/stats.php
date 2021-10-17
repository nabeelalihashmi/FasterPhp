<?php 

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