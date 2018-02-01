<?php

use Application\Service\Env\EnvService;
use Application\Service\Variable\VariableService;
use Application\Service\Etablissement\EtablissementService;

return [
    'service_manager' => [
        'invokables' => array(
            'EnvService'      => EnvService::class,
            'VariableService' => VariableService::class,
            'EtablissementService' => EtablissementService::class,
        ),
        'factories' => [

        ],
    ],
];
