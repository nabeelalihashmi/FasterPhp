<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


function get($params, Request $request, Response $response) {
    d('params', $params);
    d('request', $request->query->get('id'));
    // return $response->setContent($params);
    echo 'Hello, World: ' . 'Admin';
}

function post() {

}

function put() {

}

function delete() {

}

function before_get() {

}

function after_get() {

}