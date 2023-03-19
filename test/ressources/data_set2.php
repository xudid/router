<?php

return [
    'authorized_methods' => ['GET'],
    'routes' => [
        [
            'method' => 'GET',
            'path' => '/test/:id',
            'callback' => function () {
                echo "loaded";
            },
        ],
        [
            'method' => 'GET',
            'path' => '/test2/:id',
            'callback' => function () {
                echo "loaded2";
            },
        ]
    ]

];
