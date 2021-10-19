<?php

return [   
    ['ban', function($user) {
        echo 'banning user with id: ' . $user;
    }],

    ['demo.event', function($e = 10) {
        echo $e;
    }]
];