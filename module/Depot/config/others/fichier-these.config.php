<?php

namespace Depot;

/**
 * Config concernant les fichiers liés à une thèse.
 */

use Depot\Assertion\These\TheseAssertion;
use Depot\Controller\Factory\FichierTheseControllerFactory;
use Depot\Controller\FichierTheseController;
use Depot\Controller\Plugin\UrlFichierThese;
use Depot\Provider\Privilege\DepotPrivileges;
use Depot\Provider\Privilege\ValidationPrivileges;
use Depot\Service\FichierThese\FichierTheseService;
use Depot\Service\FichierThese\FichierTheseServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'deposes',
                    ],
                    'privileges' => ValidationPrivileges::THESE_VALIDATION_RDV_BU,
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'lister-fichiers',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_DEPOT,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'telecharger-fichier',
                        'apercevoir-fichier',
                        'apercevoir-page-de-couverture',
                    ],
                    'privileges' => DepotPrivileges::THESE_TELECHARGEMENT_FICHIER,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'televerser-fichier',
                        'supprimer-fichier',
                    ],
                    'privileges' => DepotPrivileges::THESE_DEPOT_VERSION_INITIALE,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'televerser-fichier',
                        'supprimer-fichier',
                    ],
                    'privileges' => DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'fusionnerConsole',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => FichierTheseController::class,
                    'action' => [
                        'recuperer-fusion',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'fichier' => [
                'child_routes' => [
                    'deposes' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/deposes',
                            'defaults' => [
                                'controller' => FichierTheseController::class,
                                'action' => 'deposes',
                            ],
                        ],
                    ],
                    'these' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/these/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => FichierTheseController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'lister-fichiers' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/lister-fichiers',
                                    'defaults' => [
                                        'action' => 'lister-fichiers',
                                        /* @see FichierTheseController::listerFichiersAction() */
                                    ],
                                ],
                            ],
                            'televerser' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/televerser',
                                    'defaults' => [
                                        'action' => 'televerser-fichier',
                                        /* @see FichierTheseController::televerserFichierAction() */
                                    ],
                                ],
                            ],
                            'telecharger' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/telecharger/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults' => [
                                        'action' => 'telecharger-fichier',
                                        /* @see FichierTheseController::telechargerFichierAction() */
                                    ],
                                ],
                            ],
                            'apercevoir' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/apercevoir/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults' => [
                                        'action' => 'apercevoir-fichier',
                                    ],
                                ],
                            ],
                            'apercevoir-page-de-couverture' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/apercevoir-page-de-couverture',
                                    'defaults' => [
                                        'action' => 'apercevoir-page-de-couverture',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/supprimer/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults' => [
                                        'action' => 'supprimer-fichier',
                                    ],
                                ],
                            ],
                            'recuperer-fusion' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/recuperer-fusion/:outputFile',
                                    'defaults' => [
                                        'action' => 'recuperer-fusion',
                                    ],
                                ],
                            ],
                        ],
                    ], // 'these'
                ],
            ], // 'fichier'
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'fusionner' => [
                    'options' => [
                        'route' => 'fichier fusionner --these= --versionFichier= [--removeFirstPage] [--notifier=]',
                        'defaults' => [
                            'controller' => FichierTheseController::class,
                            'action' => 'fusionnerConsole',
                        ],
                    ],
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
                            'fichiers-deposes' => [
                                'label' => 'Fichiers de thèses',
                                'route' => 'fichier/deposes',
                                'order' => 600,
                                'resource' => PrivilegeController::getResourceId(FichierTheseController::class, 'deposes'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            FichierTheseService::class => FichierTheseServiceFactory::class,
        ],
        'aliases' => [
            'FichierTheseService' => FichierTheseService::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            FichierTheseController::class => FichierTheseControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichierThese' => UrlFichierThese::class,
        ],
    ],
];
