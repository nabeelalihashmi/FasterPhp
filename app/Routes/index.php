<?php


function get() {
    
    return 'Hello';
    // return json_encode(['msg' => 'Hello, World']);
}

function post() {

}

function put() {

}

function delete() {

}

function before_get($params) {
    d('before middleware called');
    d('parms', $params);

    return true;
    
}

function after_get() {
    d('after_middleware called');
}