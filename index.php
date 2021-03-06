<?php

use Core\Classes\Session;

date_default_timezone_set('UTC');


use Tracy\Debugger;
use Core\Classes\HashId;
use Core\Classes\Emitter;




include './core/libs/general.php';
include './config/config.php';
include './config/constants.php';


// d('session', $_SESSION);

if ($api_default) {
    header('Content-Type: application/json');
}


// spl_autoload_register(function($class) {
//     if(!include_once str_replace('\\', '/', $class) . '.php') {
//         include_once './vendor/autoload.php';
//     }
// });


if ($use_composer) {

    include_once './vendor/autoload.php';


    if ($use_session) {
        Session::start();
    }

    if ($use_tracy && !$api_default) {
        Debugger::$strictMode = true;
        Debugger::enable(Debugger::DETECT, __DIR__ . '/app/logs');
    }

    if ($use_eventhandler) {
        $events = require_once('./app/eventshandlers.php');
        foreach ($events as $event) {
            Emitter::instance()->on($event[0], $event[1]);
        }
    }
}

if ($use_redbeans) {
    include './core/libs/rb.php';
    define('REDBEAN_MODEL_PREFIX', '\\App\\Models\\');
    R::setup(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
}

if (!route()) {
    pink_error_handler();
}

exit();


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
                    $route_file = $file;
                    $params = array_slice($params, $i, count($params));
                    break;
                }
            }
        }
    }

    // if (!empty($params) && $route_file === '') {
    //     $route_file = __DIR__ . '/app/Routes/index.php';
    // }
    if ($route_file === '') {
        return false;
    }
    $params = $params ?? [];

    if (include($route_file)) {


        if (isset($route_schema)) {
            $schemaResult = parseSchema($route_schema, $method, $params);
            if (!$schemaResult) {
                return false;
            }
            if (is_array($schemaResult)) {
                // d('sc_r', $schemaResult);
                if ($schemaResult[0] == 'proxy') {
                    if (($proxy_file = $schemaResult[1]) !== null) {
                        require_once(__DIR__ . '/app/Proxies/' . $proxy_file);
                    }
                    $method = $schemaResult[2];
                    $params = $schemaResult[3];
                }
            }
        }
        $retVal = true;


        // d('details of execution', $method, $params);
        // return;

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $method = 'ajax_' . $method;
        }

        if (function_exists($funcname = 'before_' . $method)) {
            $retVal = call_user_func_array($funcname, [$params]);
        }

        if ($retVal === true) {
            $output = call_user_func_array($method, $params);
            print_r($output);
        }

        if (function_exists($funcname = 'after_' . $method)) {
            $retVal = call_user_func_array($funcname, [$params]);
        }
    } else {
        // pink_error_handler();
        d('Error:', '__P_OPEN', 'Error __P_ROUTE_NOT_HANDLED');
        return false;
    }

    return true;
}

function parseSchema($schema, $method, $params) {

    if (!in_array($method, $schema['allowed_methods'], true)) {
        return false;
    }

    $proxies = $schema['methods'][$method]['proxies'] ?? null;
    if ($proxies !== null) {
        $matching_proxy = null;
        foreach ($proxies as $proxy) {
            // find_matching proxy;
            $p = trim($proxy[0], '/');
            $tempProxyParsedStr =  ""; //str_replace('?', '', $p);
            $proxy_params = explode('/', $p);
            // d('proxy_params', $proxy_params, $params);
            if (count($proxy_params) == count($params)) {
                $newParams = [];
                $newTempProxy = '/' . implode('/', $params) . '/';
                foreach ($proxy_params as $key => $value) {
                    if ($value === '?') {
                        $newParams[] = $params[$key];
                        $tempProxyParsedStr .=  '/' . $params[$key]  . '/';
                    } else {
                        $tempProxyParsedStr .= $proxy_params[$key];
                    }
                }

                $tempProxyParsedStr = str_replace('//', '/', $tempProxyParsedStr);
                // tag, file_name, method_name, params 
                // d('shot', $tempProxyParsedStr, $newTempProxy);
                if ($tempProxyParsedStr == $newTempProxy) {
                    return ['proxy', $proxy[2], $proxy[1] ?? null, $newParams];
                }
            }
        }
    }

    $f = new ReflectionFunction($method);
    $req = $f->getNumberOfRequiredParameters();
    $total = $f->getNumberOfParameters();

    if (count($params) < $req || count($params) > $total) {
        return false;
    }

    return true;
}

function handleProxy() {
}

function pink_error_handler() {
    global $use_svelte;
    if ($use_svelte == true) {
        echo "
        <!DOCTYPE html>
<html lang=\"en\">
<head>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width,initial-scale=1'>
	<title>Svelte app</title>
	<link rel='icon' type='image/png' href='" . BASEPATH . "/public/favicon.png'>
	<link rel='stylesheet' href='" . BASEPATH . "/public/build/bundle.css'>
	<script defer src='" . BASEPATH . "/public/build/bundle.js'></script>
</head>

<body>
</body>
</html>
";
        return;
    }
    header('HTTP/1.0 404 Not Found', true, 404);
    echo 'Not Found';
    exit();
}


