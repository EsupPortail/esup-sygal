<?php

namespace Application;

use Application\Controller\Factory\RapportAnnuelControllerFactory;
use Application\Controller\Factory\RapportAnnuelRechercheControllerFactory;
use Application\Controller\RapportAnnuelController;
use Application\Controller\RapportAnnuelRechercheController;
use Application\Form\Factory\RapportAnnuelFormFactory;
use Application\Form\RapportAnnuelForm;
use Application\Provider\Privilege\RapportAnnuelPrivileges;
use Application\Search\Controller\SearchControllerPluginFactory;
use Application\Service\RapportAnnuel\RapportAnnuelSearchService;
use Application\Service\RapportAnnuel\RapportAnnuelSearchServiceFactory;
use Application\Service\RapportAnnuel\RapportAnnuelService;
use Application\Service\RapportAnnuel\RapportAnnuelServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'RapportAnnuel' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELEVERSER,
                            RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER,
                            RapportAnnuelPrivileges::RAPPORT_ANNUEL_SUPPRIMER,
                        ],
                        'resources'  => ['RapportAnnuel'],
                        'assertion' => 'Assertion\\RapportAnnuel',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'consulter',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_CONSULTER,
                    'assertion' => 'Assertion\\RapportAnnuel',
                ],
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER,
                    'assertion' => 'Assertion\\RapportAnnuel',
                ],
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELEVERSER,
                    'assertion' => 'Assertion\\RapportAnnuel',
                ],
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_SUPPRIMER,
                    'assertion' => 'Assertion\\RapportAnnuel',
                ],
                [
                    'controller' => RapportAnnuelRechercheController::class,
                    'action'     => [
                        'index',
                        'filters',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_RECHERCHER,
                ],
                [
                    'controller' => RapportAnnuelRechercheController::class,
                    'action'     => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER_ZIP,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'rapport-annuel' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/rapport-annuel',
                    'defaults'      => [
                        'controller' => RapportAnnuelController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'recherche' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'controller' => RapportAnnuelRechercheController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'index' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route' => '/',
                                    'defaults'      => [
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route'       => '/filters',
                                    'defaults'    => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'telecharger-zip'     => [
                                'type'     => 'Literal',
                                'options'  => [
                                    'route' => '/telecharger-zip',
                                    'defaults' => [
                                        'action' => 'telecharger-zip',
                                        /* @see RapportAnnuelRechercheController::telechargerZipAction() */
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'consulter'     => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/consulter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                                /* @see RapportAnnuelController::consulterAction() */
                            ],
                        ],
                    ],
                    'ajouter'  => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/ajouter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'ajouter',
                                /* @see RapportAnnuelController::ajouterAction() */
                            ],
                        ],
                    ],
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:rapportAnnuel',
                            'constraints' => [
                                'rapportAnnuel' => '\d+',
                            ],
                            'defaults'      => [
                                'action' => 'telecharger',
                                /* @see RapportAnnuelController::telechargerAction() */
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/supprimer/:rapportAnnuel',
                            'constraints' => [
                                'rapportAnnuel' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                                /* @see RapportAnnuelController::supprimerAction() */
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
                    'admin' => [
                        'pages' => [
                            'rapport-annuel' => [
                                'label'    => 'Rapports annuels',
                                'route'    => 'rapport-annuel/recherche/index',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId(RapportAnnuelRechercheController::class, 'index'),
                                'privilege' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_RECHERCHER,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RapportAnnuelService::class => RapportAnnuelServiceFactory::class,
            RapportAnnuelSearchService::class => RapportAnnuelSearchServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RapportAnnuelController::class => RapportAnnuelControllerFactory::class,
            RapportAnnuelRechercheController::class => RapportAnnuelRechercheControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            'searchControllerPlugin' => SearchControllerPluginFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            RapportAnnuelForm::class => RapportAnnuelFormFactory::class,
        ],
    ],
];
