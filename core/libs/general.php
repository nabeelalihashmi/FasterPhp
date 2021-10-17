<?php

function d(...$d) {
    $total = count($d);
    if ($total == 0) {
        return;
    }
    echo '<div style="margin: 0; padding: 0;box-sizing: border-box; width: 100%;  border: 1px solid black; font-family: monospace; background-color: #efe; margin-bottom: 4px; margin-top: 4px">';
    $summary = array_shift($d);
    $state = '';
    $state = 'open';
    if ($d[0] === '__P_OPEN') {
        $state = 'open';
        array_shift($d);
    }
    echo"<details $state ><summary style='padding: 10px;background-color: #fee; padding: 8px; border: .2px solid #ccc'>$summary</summary><pre style='background-color: #efe; padding: 4px;'>";
    foreach($d as $i => $v) { 
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        print "<span style='font-weight: bold'>$file:$line</span><br>";
        var_dump($v);
        echo '<br>';
        if ($i !== $total - 1) echo '<div style="padding: 0px;background-color: #fee;border: .2px solid #ddd; margin-bottom: 8px"></div>';
    }
    echo '</pre></div>';
    echo "</details>";
}