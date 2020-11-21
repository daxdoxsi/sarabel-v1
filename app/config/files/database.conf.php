<?php

$config['database'] = [
    'mysql' => [
        'host' => env('db.mysql.host'),
        'port' => env('db.mysql.port'),
        'user' => env('db.mysql.user'),
        'pass' => env('db.mysql.pass'),
        'name' => env('db.mysql.name'),
    ],
    'integrity_check' => [
        'project',
        'section',
        'story',
        'tag',
        'task',
        'user'
    ],
];

// Loading models
