<?php

use Application\Controller\Factory\DoctorantControllerFactory;
use Application\Provider\Privilege\DoctorantPrivileges;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\Doctorant\DoctorantServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
//        'rule_providers'     => [
//            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [],
//                ],
//            ],
//        ],
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
                            'route'       => '/modifier-persopass[/:back]',
                            'defaults'    => [
                                'action' => 'modifier-persopass',
                                'back' => 0
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
                    'modifier-persopass' => [
                        'visible'  => false,
                        'label'    => "Saisie de l'adresse électronique de contact",
                        'route'    => 'doctorant/modifier-persopass',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\Doctorant', 'modifier-persopass'),
                    ],
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
        'factories' => [
            'DoctorantService' => DoctorantServiceFactory::class,
        ],
        'aliases' => [
            DoctorantService::class => 'DoctorantService',
        ]
    ],
    'controllers'     => [
        'invokables' => [],
        'factories' => [
            'Application\Controller\Doctorant' => DoctorantControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlDoctorant'              => 'Application\Controller\Plugin\UrlDoctorant',
        ],
    ],
];
