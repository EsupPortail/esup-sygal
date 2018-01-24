<?php

use Application\Service\Env\EnvService;
use Application\Service\Variable\VariableService;

return [
    'service_manager' => [
        'invokables' => array(
            'EnvService'      => EnvService::class,
            'VariableService' => VariableService::class,
        ),
        'factories' => [

        ],
    ],
];
