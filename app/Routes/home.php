<?php

use Symfony\Component\HttpFoundation\Request;

// get handler
function get() {
    echo file_get_contents('frontend/public/index.html');
}

function post() {
    
    $output = __handle();
    return 'post';
}

function ajax_post() {
    
    $output = __handle();
    $output = json_encode($output);
    return $output;
}



function __handle() {
    $request = Request::createFromGlobals();
    $first_name = $request->request->get('first_name');
    $last_name = $request->request->get('last_name');

    /*
        Process Data
    */

    $output = ['first_name' => $first_name, 'last_name' => $last_name];
    return $output;

}
