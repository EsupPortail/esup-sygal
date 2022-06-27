<?php

use Application\Service\Variable\VariableService;
use Structure\Service\Etablissement\EtablissementService;

return [
    'service_manager' => [
        'invokables' => array(
            'VariableService' => VariableService::class,
        ),
        'factories' => [

        ],
    ],
];
