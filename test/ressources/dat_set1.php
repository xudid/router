<?php

return [
    'authorized_methods' => ['GET'],
    'routes' =>[
        [
            'action' => [
                'description' => 'test',
                'type' => 'SHOW'
            ],
            'method' => 'GET',
            'name' => 'test_show',
            'path' => '/test/:id',
            'callback' => function () {
                echo "loaded";
            },
            'params' => [['id' => '[0-9]+']]
        ]
    ]
];
