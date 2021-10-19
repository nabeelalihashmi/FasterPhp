<?php

function handle() {

    $routes_path = $GLOBALS['APP_DIR'] . '/app/Routes';

    $biggus_diccus = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($routes_path));
    foreach ($biggus_diccus as $file) {
        if ($file->isDir()) {
            continue;
        }

        $script = $file->getPathname();
        $end_point = str_replace($routes_path, '', $script);
        $end_point = str_replace('/index.php', '', $end_point);
        $end_point = rtrim($end_point, '.php')  . '/';
        $funcs = get_defined_functions_in_file($script);


        $mask = "%10.10s | %-40.40s\n";
        $mask2 = "| %-40s |\n";
        $mask3 = " %-40s \n";
        
        
        printf($mask3, str_repeat('-', strlen($script) + 2));
        printf($mask2, $script );
        printf($mask3, str_repeat('-', strlen($script) + 2));
        if (count($funcs) == 0) { 
            printf($mask, 'N/A', ' Empty File ');
        }
        foreach ($funcs as $f) {
            $f = strtoupper($f);
            if (strpos($f, 'PROXY') === 0) {
                // $f = str_replace('PROXY_', '', $f);
                $e = explode('_', $f);
                $f = '[PROXY]' . $e[1];
                printf($mask, strtoupper($f), $end_point);
            }
            
            elseif (strpos($f, 'BEFORE') === 0 || (strpos($f, 'AFTER') === 0)) {
                
            } else {
                printf($mask, strtoupper($f), $end_point);
            }
        }
        // printf($mask3, str_repeat('-', strlen($script) + 2));
    }
}


function get_defined_functions_in_file($file) {
    $source = file_get_contents($file);
    $tokens = token_get_all($source);
    
    $functions = array();
    $nextStringIsFunc = false;
    $inClass = false;
    $bracesCount = 0;

    foreach ($tokens as $token) {
        switch ($token[0]) {
            case T_CLASS:
                $inClass = true;
                break;
            case T_FUNCTION:
                if (!$inClass) $nextStringIsFunc = true;
                break;

            case T_STRING:
                if ($nextStringIsFunc) {
                    $nextStringIsFunc = false;
                    $functions[] = $token[1];
                }
                break;

                // Anonymous functions
            case '(':
            case ';':
                $nextStringIsFunc = false;
                break;

                // Exclude Classes
            case '{':
                if ($inClass) $bracesCount++;
                break;

            case '}':
                if ($inClass) {
                    $bracesCount--;
                    if ($bracesCount === 0) $inClass = false;
                }
                break;
        }
    }

    return $functions;
}
