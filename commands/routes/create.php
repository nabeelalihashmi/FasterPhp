<?php

function handle($route, $methods = "", $middlewares_before = "", $middlewares_after = "", $mode = null, $force_override = 'N') {

    $methods = strtolower($methods);
    $middlewares_before = strtolower($middlewares_before);
    $middlewares_after = strtolower($middlewares_after);

    $route = trim($route, '');
    $route = trim($route, '/');
    echo "Creating route $route\r\n";
    if ($mode === null) {
        echo "Select the method.\r\n[1]. $route/index.php\r\n[2]. $route.php\r\n> ";
        $mode = fgets(STDIN);
        $mode = trim($mode);
    }
    $output_file = '';
    switch ($mode) {
        case '1':
            $output_file = $GLOBALS['APP_DIR'] . '/app/Routes/' . $route . '/index.php';
            break;
        case '2':
            $output_file = $GLOBALS['APP_DIR'] . '/app/Routes/' . $route . '.php';
            break;
        default:
            die('Invalid Command');
            break;
    }

    if (file_exists($output_file) && $force_override == 'N') {
        echo "File Exist, Do you want to overwrite it? [Y/N].\r\n";
        $force_override = fgets(STDIN);
        $force_override = strtolower(trim($force_override));

        if ($force_override !== 'y') {
            die('Exiting');
        }

    }

    if ($methods === "") {
        echo "Enter the handlers.(comma separated, e.g get,post,put\r\n>";
        $methods = fgets(STDIN);
        $methods = trim($methods);
    }

    if ($middlewares_before === "") {
        echo "Enter the before middlewares.(comma separated, e.g get,post,put)\r\n>";
        $middlewares_before = fgets(STDIN);
        $middlewares_before = trim($middlewares_before);
    }
    if ($middlewares_after === "") {
        echo "Enter the after middlewares.(comma separated, e.g get,post,put)\r\n>";
        $middlewares_after = fgets(STDIN);
        $middlewares_after = trim($middlewares_after);
    }

    var_dump($middlewares_after);
    echo "Writing File: $output_file\r\n";
    $tpl_file = $GLOBALS['APP_DIR'] . '/commands/templates/route_template.txt';
    $template = file_get_contents($tpl_file);
    $methods = explode(',', $methods);
    $middlewares_before = explode(',', $middlewares_before);
    $middlewares_after = explode(',', $middlewares_after);

    $methods_string = "['" . implode(",'", $methods) . "']";
    $template = str_replace('__ALLOWED_METHODS__', $methods_string, $template);

    if (count($methods) > 0) {
        foreach ($methods as $m) {
            $template .= "\r\nfunction $m() {} \r\n\r\n";
        }
    }

    if (count($middlewares_before) > 0) {
        foreach ($middlewares_before as $m) {
            $template .= "\r\nfunction before_$m() {}\r\n\r\n";
        }
    }

    if (count($middlewares_after) > 0) {
        foreach ($middlewares_after as $m) {
            $template .= "\r\nfunction after_$m() {}\r\n\r\n";
        }
    }


    if (!file_exists($output_file)) {
        suckDir($output_file);
        file_put_contents($output_file, $template);
    }

    echo 'Done! ';
}


function suckDir($filename) {
    $info = pathinfo($filename, PATHINFO_DIRNAME);
    if (!is_dir($info)) {
        echo "\nMaking directory\n";
        mkdir($info, 0777, true);
    }
}