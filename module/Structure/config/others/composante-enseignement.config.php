<?php

use Structure\Service\ComposanteEnseignement\ComposanteEnseignementService;
use Structure\Service\ComposanteEnseignement\ComposanteEnseignementServiceFactory;

return [
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            'ComposanteEnseignementService' => ComposanteEnseignementServiceFactory::class,
        ],
        'aliases' => [
            ComposanteEnseignementService::class => 'ComposanteEnseignementService',
        ]
    ],
];
