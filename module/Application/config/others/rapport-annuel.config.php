<?php

namespace Application;

use Application\Controller\Factory\RapportAnnuelControllerFactory;
use Application\Controller\RapportAnnuelController;
use Application\Form\Factory\RapportAnnuelFormFactory;
use Application\Form\RapportAnnuelForm;
use Application\Provider\Privilege\RapportAnnuelPrivileges;
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
                            RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER,
                            RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELEVERSER,
                        ],
                        'resources'  => ['RapportAnnuel'],
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'lister',
                        'telecharger',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELECHARGER,
                ],
                [
                    'controller' => RapportAnnuelController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                    ],
                    'privileges' => RapportAnnuelPrivileges::RAPPORT_ANNUEL_TELEVERSER,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'rapport-annuel' => [
                'type'          => 'Segment',
                'options'       => [
                    'route' => '/rapport-annuel',
                    'defaults'      => [
                        'controller' => RapportAnnuelController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'rechercher' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'action' => 'recherche',
                            ],
                        ],
                    ],
                    'lister'     => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/lister/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'lister',
                                /* @see RapportAnnuelController::listerAction() */
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
                                'route'    => 'rapport-annuel/recherche',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId(RapportAnnuelController::class, 'recherche'),
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
            //RapportAnnuelSearchService::class => RapportAnnuelSearchServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RapportAnnuelController::class => RapportAnnuelControllerFactory::class,
            //RapportAnnuelRechercheController::class => RapportAnnuelRechercheControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlRapportAnnuel' => UrlRapportAnnuel::class,
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
