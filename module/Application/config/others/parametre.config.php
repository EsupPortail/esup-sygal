<?php

use Application\Service\Parametre\ParametreService;

return [
    'service_manager' => [
        'invokables' => array(
            'ParametreService' => ParametreService::class,
        ),
        'factories' => [

        ],
    ],
];
