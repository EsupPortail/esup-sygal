<?php

namespace UnicaenLivelog;

return [
    'unicaen-livelog' => [
        'websocket' => [
            'private_url' => '0.0.0.0:7443',
            'public_url' => '/livelog',
            'verbose' => false,
        ],
        'socket' => [
            'path' => 'unix:///tmp/unicaen_livelog.sock',
            'verbose' => false,
        ]
    ],
];
