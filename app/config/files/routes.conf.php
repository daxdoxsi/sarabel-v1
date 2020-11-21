<?php

$routes = [

    # System controller
    '' => [
        'controller'=> 'main',
        'function'  => 'index',
        'initializers' => [
            'global',
        ]
    ],
    'page-not-found' => [
        'controller'=> 'system',
        'function'  => 'page_not_found',
        'initializers' => [
            'global',
        ]
    ],
    'maintenance' => [
        'controller'=> 'system',
        'function'  => 'maintenance',
        'initializers' => [
            'global',
        ]
    ],
    'logout' => [
        'controller'=> 'system',
        'function'  => 'logout',
        'initializers' => [
            'global',
        ]
    ],

];
