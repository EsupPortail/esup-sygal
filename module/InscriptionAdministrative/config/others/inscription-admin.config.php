<?php

namespace InscriptionAdministrative;

use InscriptionAdministrative\Controller\InscriptionAdministrativeController;
use InscriptionAdministrative\Controller\InscriptionAdministrativeControllerFactory;
use InscriptionAdministrative\Controller\Recherche\InscriptionAdministrativeRechercheController;
use InscriptionAdministrative\Controller\Recherche\InscriptionAdministrativeRechercheControllerFactory;
use InscriptionAdministrative\Listener\ImportProcessListener;
use InscriptionAdministrative\Listener\ImportProcessListenerFactory;
use InscriptionAdministrative\Process\Reception\ReceptionProcess;
use InscriptionAdministrative\Process\Reception\ReceptionProcessFactory;
use InscriptionAdministrative\Rule\Reception\ReceptionRule;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleFactory;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManagerFactory;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRule;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRule;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRuleFactory;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use InscriptionAdministrative\Service\InscriptionAdministrativeServiceFactory;
use InscriptionAdministrative\Service\Search\InscriptionAdministrativeSearchService;
use InscriptionAdministrative\Service\Search\InscriptionAdministrativeSearchServiceFactory;
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
                    'controller' => InscriptionAdministrativeRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => InscriptionAdministrativeController::class,
                    'action' => [
                        'voir',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => InscriptionAdministrativeController::class,
                    'action' => [
                        'process',
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
                        /** @see InscriptionAdministrativeController::indexAction() */
                        'controller' => InscriptionAdministrativeController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'recherche' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => InscriptionAdministrativeRechercheController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'index' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/index',
                                    'defaults' => [
                                        /** @see InscriptionAdministrativeRechercheController::indexAction() */
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        /** @see InscriptionAdministrativeRechercheController::filtersAction() */
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'voir' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/voir/:id',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                /** @see InscriptionAdministrativeController::voirAction() */
                                'action' => 'voir',
                            ],
                        ],
                    ],
                    'process' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/process/:id',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults' => [
                                /** @see InscriptionAdministrativeController::processAction() */
                                'action' => 'process',
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
            InscriptionAdministrativeRechercheController::class => InscriptionAdministrativeRechercheControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            InscriptionAdministrativeService::class => InscriptionAdministrativeServiceFactory::class,
            InscriptionAdministrativeSearchService::class => InscriptionAdministrativeSearchServiceFactory::class,
            ImportProcessListener::class => ImportProcessListenerFactory::class,
            ReceptionProcess::class => ReceptionProcessFactory::class,
            ReceptionRulePluginManager::class => ReceptionRulePluginManagerFactory::class,
        ]
    ]
];
