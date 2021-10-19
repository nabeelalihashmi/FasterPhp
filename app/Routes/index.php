<?php



use Core\Classes\Mailer;
use Symfony\Component\HttpFoundation\Request;

$route_schema = [
    'allowed_methods' => ['get', 'post', 'delete', 'put'],
    'methods' => [
        'get' => [
            'allowed_params' => 2,
            'allowed_types' => []
        ]
    ]
];

/**
 *  get index
 *  handles = /x/y/z
 * @param [type] $username
 * @param integer $param_2
 * @param integer $param_3
 * @return void
 */

function get($username = null , $param_2 = 10, $param_3 = 50) {
    // d('param1', $username);
    // d('param2', $param_2);
    return 'Hello';
    // return json_encode(['msg' => 'Hello, World']);
}

function post() {
    $request = Request::createFromGlobals();
    $email =  $request->request->get('email');
    $name =  $request->request->get('name');
    echo "name: $name, email = $email";
    $result = Mailer::mail('Test Email', [$email => $email], 'Hi', 'hi');
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

function before_get($params) {;
    // d('params_in_middleware', $params);

    return true;
}

function after_get() {
    // d('after_middleware called');
}


//  Write Functins below