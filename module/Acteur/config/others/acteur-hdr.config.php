<?php

namespace Acteur;

use Acteur\Assertion\ActeurHDR\ActeurHDRAssertion;
use Acteur\Assertion\ActeurHDR\ActeurHDRAssertionFactory;
use Acteur\Controller\ActeurHDR\ActeurHDRController;
use Acteur\Controller\ActeurHDR\ActeurHDRControllerFactory;
use Acteur\Fieldset\ActeurHDR\ActeurHDRFieldset;
use Acteur\Fieldset\ActeurHDR\ActeurHDRFieldsetFactory;
use Acteur\Fieldset\ActeurHDR\ActeurHDRHydrator;
use Acteur\Fieldset\ActeurHDR\ActeurHDRHydratorFactory;
use Acteur\Form\ActeurHDR\ActeurHDRForm;
use Acteur\Form\ActeurHDR\ActeurHDRFormFactory;
use Acteur\Provider\Privilege\ActeurPrivileges;
use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurHDR\ActeurHDRServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'ActeurHDR' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            ActeurPrivileges::MODIFIER_ACTEUR_SES_THESES,
                            ActeurPrivileges::MODIFIER_ACTEUR_TOUTES_THESES,
                        ],
                        'resources' => ['ActeurHDR'],
                        'assertion' => \Acteur\Assertion\ActeurHDR\ActeurHDRAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ActeurHDRController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        ActeurPrivileges::MODIFIER_ACTEUR_SES_THESES,
                        ActeurPrivileges::MODIFIER_ACTEUR_TOUTES_THESES,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'hdr' => [
                'child_routes' => [
                    'acteur' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/acteur/:acteur',
                            'constraints' => [
                                'acteur' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => ActeurHDRController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'modifier' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/modifier',
                                    'defaults' => [
                                        'action' => 'modifier',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            // DEPTH = 0
            'home' => [
                'pages' => [
//                    'xxxxxxxxxxxxx' => [
//                        'order' => -50,
//                        'label' => 'Acteurs de thèses',
//                        'route' => 'these/acteur',
//                        'params' => [],
//                        'query' => ['etatHDR' => 'E'],
//                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
//                        'pages' => [
//                            // PAS de pages filles sinon le menu disparaît ! :-/
//                        ]
//                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            ActeurHDRService::class => ActeurHDRServiceFactory::class,
            ActeurHDRAssertion::class => ActeurHDRAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ActeurHDRController::class => ActeurHDRControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            ActeurHDRFieldset::class => ActeurHDRFieldsetFactory::class,
            ActeurHDRForm::class => ActeurHDRFormFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            ActeurHDRHydrator::class => ActeurHDRHydratorFactory::class,
            Hydrator\ActeurHDR\ActeurHDRHydrator::class => Hydrator\ActeurHDR\ActeurHDRHydratorFactory::class,
        ],
    ],
];
