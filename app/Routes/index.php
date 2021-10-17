<?php

use Core\Classes\Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function get($param_1, $param_2, Request $request, Response $response) {
    d('param1', $param_1);
    d('param2', $param_2);
    return 'Hello';
    // return json_encode(['msg' => 'Hello, World']);
}

function post(Request $request) {
    $email =  $request->request->get('email');
    $name =  $request->request->get('name');
    echo "name: $name, email = $email";
    $result = Mailer::mail('Test Email', [$email => $email ], 'Hi', 'hi');
    if ($result) {
        echo 'Mail Sent';
    } else {
        echo 'Error';
    }
}

function put() {

}

function delete() {

}

function before_get($params) {
    d('before middleware called');
    d('params_in_middleware', $params);

    return true;
    
}

function after_get() {
    d('after_middleware called');
}