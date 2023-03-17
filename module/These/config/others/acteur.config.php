<?php

namespace These;

use Laminas\Router\Http\Segment;
use These\Assertion\Acteur\ActeurAssertion;
use These\Assertion\Acteur\ActeurAssertionFactory;
use These\Controller\ActeurController;
use These\Controller\Factory\ActeurControllerFactory;
use These\Fieldset\Acteur\ActeurFieldset;
use These\Fieldset\Acteur\ActeurFieldsetFactory;
use These\Fieldset\Acteur\ActeurHydrator;
use These\Fieldset\Acteur\ActeurHydratorFactory;
use These\Form\Acteur\ActeurForm;
use These\Form\Acteur\ActeurFormFactory;
use These\Provider\Privilege\ActeurPrivileges;
use These\Service\Acteur\ActeurService;
use These\Service\Acteur\ActeurServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
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
                        'resources' => ['Acteur'],
                        'assertion' => ActeurAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ActeurController::class,
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
                                'controller' => ActeurController::class,
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
            ActeurService::class => ActeurServiceFactory::class,
            ActeurAssertion::class => ActeurAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ActeurController::class => ActeurControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            ActeurFieldset::class => ActeurFieldsetFactory::class,
            ActeurForm::class => ActeurFormFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            ActeurHydrator::class => ActeurHydratorFactory::class,
        ],
    ],
];
