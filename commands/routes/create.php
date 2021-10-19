<?php

function handle($route = null, $mode = null) {
    $routes_path = $GLOBALS['APP_DIR'] . '/app/Routes';
    
    if (!$route) {
        echo "s\r\n";
    }
    if ($mode === null) {
        echo "Select the method.\r\n [1]. directory/index.php\r\n[2]. route_name.php ";
    }
    switch($mode) { 
        case '0'
    }
}