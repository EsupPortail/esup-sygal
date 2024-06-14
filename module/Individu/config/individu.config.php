<?php

namespace Individu;

use BjyAuthorize\Provider\Resource\Config;
use Individu\Controller\IndividuComplController;
use Individu\Assertion\IndividuAssertion;
use Individu\Assertion\IndividuAssertionFactory;
use Individu\Controller\IndividuController;
use Individu\Controller\IndividuControllerFactory;
use Individu\Form\IndividuForm;
use Individu\Form\IndividuFormFactory;
use Individu\Hydrator\IndividuHydrator;
use Individu\Hydrator\IndividuHydratorFactory;
use Individu\Hydrator\Strategy\IndividuStrategy;
use Individu\Hydrator\Strategy\IndividuStrategyFactory;
use Individu\Provider\Privilege\IndividuPrivileges;
use Individu\Service\IndividuService;
use Individu\Service\IndividuServiceFactory;
use Individu\Service\Search\IndividuSearchService;
use Individu\Service\Search\IndividuSearchServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            Config::class => [
                'Individu' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privilege' => [
                            IndividuPrivileges::INDIVIDU_CONSULTER,
                            IndividuPrivileges::INDIVIDU_MODIFIER,
                            IndividuPrivileges::INDIVIDU_SUPPRIMER,
                        ],
                        'resources' => 'Individu',
                        //'assertion' => 'xxx',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuController::class,
                    'action' => ['index', 'rechercher'],
                    'privileges' => IndividuPrivileges::INDIVIDU_LISTER,
                ],
                [
                    'controller' => IndividuController::class,
                    'action' => ['voir'],
                    'privileges' => IndividuPrivileges::INDIVIDU_CONSULTER,
                ],
                [
                    'controller' => IndividuController::class,
                    'action' => ['ajouter'],
                    'privileges' => IndividuPrivileges::INDIVIDU_AJOUTER,
                ],
                [
                    'controller' => IndividuController::class,
                    'action' => ['modifier'],
                    'privileges' => IndividuPrivileges::INDIVIDU_MODIFIER,
                ],
                [
                    'controller' => IndividuController::class,
                    'action' => ['supprimer', 'restaurer'],
                    'privileges' => IndividuPrivileges::INDIVIDU_SUPPRIMER,
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'individu' => [
                                'label' => 'Individus',
                                'route' => 'individu',
                                'resource' => PrivilegeController::getResourceId(IndividuController::class, 'index'),
                                'icon' => "fas fa-user-friends",
                                'order' => 64,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/individu',
                    'defaults' => [
                        'controller' => IndividuController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'voir' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/voir/:individu',
                            'defaults' => [
                                'action' => 'voir',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:individu',
                            'defaults' => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:individu',
                            'defaults' => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/restaurer/:individu',
                            'defaults' => [
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter[/:utilisateur]',
                            'defaults' => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'rechercher' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/rechercher',
                            'defaults' => [
                                'controller' => IndividuController::class,
                                'action' => 'rechercher',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuController::class => IndividuControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            IndividuService::class => IndividuServiceFactory::class,
            IndividuSearchService::class => IndividuSearchServiceFactory::class,
            IndividuAssertion::class => IndividuAssertionFactory::class,
            IndividuStrategy::class => IndividuStrategyFactory::class,
        ],
        'aliases' => [
            'IndividuService' => IndividuService::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            IndividuForm::class => IndividuFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            IndividuHydrator::class => IndividuHydratorFactory::class,
        ],
    ],
];