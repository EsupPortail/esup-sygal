<?php

namespace Application;

use Application\Assertion\RapportActivite\RapportActiviteAssertion;
use Application\Controller\Factory\RapportActiviteControllerFactory;
use Application\Controller\Factory\RapportActiviteRechercheControllerFactory;
use Application\Controller\RapportActiviteController;
use Application\Controller\RapportActiviteRechercheController;
use Application\Form\Factory\RapportActiviteFormFactory;
use Application\Form\RapportActiviteForm;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Search\Controller\SearchControllerPluginFactory;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Rapport\RapportSearchServiceFactory;
use Application\Service\Rapport\RapportService;
use Application\Service\Rapport\RapportServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Rapport' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            RapportPrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                        ],
                        'resources'  => ['Rapport'],
                        'assertion' => 'Assertion\\RapportActivite', /** @see RapportActiviteAssertion */
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'consulter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                    ],
                    'assertion' => 'Assertion\\RapportActivite',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                    ],
                    'assertion' => 'Assertion\\RapportActivite',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                    ],
                    'assertion' => 'Assertion\\RapportActivite',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                    ],
                    'assertion' => 'Assertion\\RapportActivite',
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action'     => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                    ],
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action'     => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_ZIP,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'rapport-activite' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/rapport-activite',
                    'defaults'      => [
                        'controller' => RapportActiviteController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'recherche' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'controller' => RapportActiviteRechercheController::class,
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
                                        /* @see RapportActiviteRechercheController::telechargerZipAction() */
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
                                /* @see RapportActiviteController::consulterAction() */
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
                                /* @see RapportActiviteController::ajouterAction() */
                            ],
                        ],
                    ],
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'      => [
                                'action' => 'telecharger',
                                /* @see RapportActiviteController::telechargerAction() */
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/supprimer/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                                /* @see RapportActiviteController::supprimerAction() */
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
                            'rapport-activite' => [
                                'label'    => "Rapports d'activité",
                                'route'    => 'rapport-activite/recherche/index',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId(RapportActiviteRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                                    RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RapportService::class => RapportServiceFactory::class,
            RapportSearchService::class => RapportSearchServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RapportActiviteController::class => RapportActiviteControllerFactory::class,
            RapportActiviteRechercheController::class => RapportActiviteRechercheControllerFactory::class,
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
            RapportActiviteForm::class => RapportActiviteFormFactory::class,
        ],
    ],
];
