<?php

use Application\Service\Variable\VariableService;

return [
    'service_manager' => [
        'invokables' => array(
            'VariableService' => VariableService::class,
        ),
        'factories' => [

        ],
    ],
];
