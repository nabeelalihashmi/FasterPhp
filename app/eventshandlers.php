<?php

return [   
    ['invalidtry.maxed', function($user) {
        echo 'banning user with id: ';
    }],

    ['demo.event', function($e = 10) {
        echo $e;
    }]

];