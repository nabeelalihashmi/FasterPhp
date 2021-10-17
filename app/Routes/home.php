<?php 

function get($params) {
    echo 'Calling Home';
    d('page:' , 'home');
    d('params:' , $params);
}