<?php

namespace InscriptionAdministrative;

use InscriptionAdministrative\Controller\InscriptionAdministrativeController;
use InscriptionAdministrative\Controller\InscriptionAdministrativeControllerFactory;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'InscriptionAdministrative' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
//                    [
//                        'privileges' => [
//
//                        ],
//                        'resources' => ['InscriptionAdministrative'],
//                    ],
//                    [
//                        'privileges' => [
//
//                        ],
//                        'resources' => ['InscriptionAdministrative'],
//                        'assertion' => InscriptionAdministrativeAssertion::class,
//                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InscriptionAdministrativeController::class,
                    'action' => [
                        'index',
                        'voir',
                    ],
                    'roles' => 'user',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'inscription-administrative' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/inscription-administrative',
                    'defaults' => [
                        'controller' => InscriptionAdministrativeController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'voir' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/voir/:id',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'voir',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            InscriptionAdministrativeController::class => InscriptionAdministrativeControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            InscriptionAdministrativeService::class => InscriptionAdministrativeServiceFactory::class,
        ]
    ]
];
