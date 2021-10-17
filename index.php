<?php

date_default_timezone_set('UTC');

use Tracy\Debugger;
use Core\Classes\Emitter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


include './vendor/autoload.php';
include './libs/general.php';
include './config/config.php';


if ($use_tracy) {
    Debugger::$strictMode = true;
    Debugger::enable(Debugger::DETECT, __DIR__ . '/app/logs');
}

if ($use_eventhandler) {    
    $events = require_once('./app/eventshandlers.php');
    foreach ($events as $event) {
        Emitter::instance()->on($event[0], $event[1]);
    }
}

if ($use_redbeans) {
    class_alias('\RedBeanPHP\R', '\R');
    define('REDBEAN_MODEL_PREFIX', '\\App\\Models\\');
    R::setup(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
}


route();

function route() {
    $script = $_SERVER['SCRIPT_NAME'];
    $dirname = dirname($script);
    $dirname = $dirname === '/' ? '' : $dirname;
    $basename = basename($script);
    $uri = strtolower(str_replace([$dirname, $basename], "", $_SERVER['REQUEST_URI']));
    
    $uri = parse_url($uri, PHP_URL_PATH);
    
    // $uri = trim($uri);
    $uri = trim($uri, '/');
    $method = strtolower($_SERVER['REQUEST_METHOD']);
    
    $methods = ['put', 'post', 'patch', 'delete', 'head'];
    if ($method == 'post') {
        if (isset($_POST['__method']) && in_array($__method = strtolower($_POST['__method']), $methods)) {
            $method = $__method;
        }
    }
    
    $params = explode('/', $uri);
    $route_file = '';
    if ($params[0] == '/') {
        $route_file = __DIR__ . '/app/Routes/index.php';
        $params = [];
    } else {
        for ($i = count($params); $i > 0; $i--) {
            $newParams = array_slice($params, 0, $i);
            $r = __DIR__ . '/app/Routes/' . implode('/', $newParams);
            
            if (file_exists($file = $r . '.php')) {
                $route_file = $file;
                $params = array_slice($params, $i, count($params));
                break;
            }
            
            if (is_dir($r)) {
                $file_name = $params[0] === '/' ? 'index.php' : '/index.php';
                if (file_exists($file = $r . $file_name)) {
                    // d('dir', '__P_OPEN', $file);
                    $route_file = $file;
                    $params = array_slice($params, $i, count($params));
                    break;
                }
            }
        }
    }
    if ($route_file === '') {
        pink_error_handler();
        return;
    }
    $params = $params ?? [];
    $request = $request ?? Request::createFromGlobals();
    $response = $response ?? new Response('', Response::HTTP_OK, ['content-type' => 'text/html']);
    $params[] = $request;
    $params[] = $response;


    if (include($route_file)) {

        $retVal = true;
        if (function_exists($funcname = 'before_' . $method)) {
            $retVal = call_user_func_array($funcname, $params);
        }

        if ($retVal === true) {
            $output = call_user_func_array($method, $params);
            print_r($output);
        }

        if (function_exists($funcname = 'after_' . $method)) {
            $retVal = call_user_func_array($funcname, $params);
        }

    } else {
        d('Error:', '__P_OPEN', 'Error __P_ROUTE_NOT_HANDLED');
    }
    
    exit();
}
    
    
function pink_error_handler() {
    header('HTTP/1.0 404 Not Found', true, 404);
    echo 'Page Not Found';
    exit();
}

