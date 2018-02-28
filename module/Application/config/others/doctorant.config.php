<?php

use Application\Controller\DoctorantController;
use Application\Provider\Privilege\DoctorantPrivileges;
use Application\Service\Doctorant\DoctorantService;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            DoctorantPrivileges::DOCTORANT_MODIFICATION_PERSOPASS,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => 'Assertion\\These',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Doctorant',
                    'action'     => [
                        'modifier-persopass',
                    ],
                    'privileges' => DoctorantPrivileges::DOCTORANT_MODIFICATION_PERSOPASS,
                    'assertion'  => 'Assertion\\These',
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'doctorant' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/doctorant/:doctorant',
                    'constraints' => [
                        'doctorant' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Doctorant',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'modifier-persopass' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/modifier-persopass[/:detournement]',
                            'constraints' => [
                                'detournement' => '\d',
                            ],
                            'defaults'    => [
                                'action' => 'modifier-persopass',
                                'detournement' => 0
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'these' => [
                        'pages' => [
                            'modifier-persopass' => [
                                'visible'  => false,
                                'label'    => 'Saisie du persopass',
                                'route'    => 'doctorant/modifier-persopass',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Doctorant', 'modifier-persopass'),
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
    'hydrators' => array(
        'factories' => array(
        )
    ),
    'service_manager' => [
        'invokables' => array(
            'DoctorantService' => DoctorantService::class,
        ),
        'factories' => [

        ],
        'aliases' => [
            DoctorantService::class => 'DoctorantService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Doctorant' => DoctorantController::class,
        ],
        'factories' => [
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlDoctorant'              => 'Application\Controller\Plugin\UrlDoctorant',
        ],
    ],
];
