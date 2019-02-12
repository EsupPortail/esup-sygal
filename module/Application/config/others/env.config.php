<?php

use Application\Service\Variable\VariableService;
use Application\Service\Etablissement\EtablissementService;

return [
    'service_manager' => [
        'invokables' => array(
            'VariableService' => VariableService::class,
        ),
        'factories' => [

        ],
    ],
];
