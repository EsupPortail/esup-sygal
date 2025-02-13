<?php

namespace Acteur;

use Acteur\Assertion\ActeurThese\ActeurTheseAssertion;
use Acteur\Assertion\ActeurThese\ActeurTheseAssertionFactory;
use Acteur\Controller\ActeurThese\ActeurTheseController;
use Acteur\Controller\ActeurThese\ActeurTheseControllerFactory;
use Acteur\Fieldset\ActeurThese\ActeurTheseFieldset;
use Acteur\Fieldset\ActeurThese\ActeurTheseFieldsetFactory;
use Acteur\Fieldset\ActeurThese\ActeurTheseHydrator;
use Acteur\Fieldset\ActeurThese\ActeurTheseHydratorFactory;
use Acteur\Form\ActeurThese\ActeurTheseForm;
use Acteur\Form\ActeurThese\ActeurTheseFormFactory;
use Acteur\Provider\Privilege\ActeurPrivileges;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Acteur\Service\ActeurThese\ActeurTheseServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'ActeurThese' => [],
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
                        'resources' => ['ActeurThese'],
                        'assertion' => ActeurTheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ActeurTheseController::class,
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
            'these' => [
                'child_routes' => [
                    'acteur' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/acteur/:acteur',
                            'constraints' => [
                                'acteur' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => ActeurTheseController::class,
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
//                        'query' => ['etatThese' => 'E'],
//                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
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
            ActeurTheseService::class => ActeurTheseServiceFactory::class,
            ActeurTheseAssertion::class => ActeurTheseAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ActeurTheseController::class => ActeurTheseControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            ActeurTheseFieldset::class => ActeurTheseFieldsetFactory::class,
            ActeurTheseForm::class => ActeurTheseFormFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            ActeurTheseHydrator::class => ActeurTheseHydratorFactory::class,
        ],
    ],
];
