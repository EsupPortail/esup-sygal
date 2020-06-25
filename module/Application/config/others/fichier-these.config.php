<?php

/**
 * Config concernant les fichiers liés à une thèse.
 */

use Application\Controller\Factory\FichierTheseControllerFactory;
use Application\Controller\FichierTheseController;
use Application\Controller\Plugin\UrlFichierThese;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\FichierThese\FichierTheseServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'deposes',
                    ],
                    'privileges' => ValidationPrivileges::THESE_VALIDATION_RDV_BU,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'lister-fichiers',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_DEPOT,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'telecharger-fichier',
                        'apercevoir-fichier',
                    ],
                    'privileges' => ThesePrivileges::THESE_TELECHARGEMENT_FICHIER,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'apercevoir-page-de-couverture',
                    ],
                    'role' => ThesePrivileges::THESE_TELECHARGEMENT_FICHIER,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'televerser-fichier',
                        'supprimer-fichier',
                    ],
                    'privileges' => ThesePrivileges::THESE_DEPOT_VERSION_INITIALE,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'televerser-fichier',
                        'supprimer-fichier',
                    ],
                    'privileges' => ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
                        'fusionnerConsole',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action'     => [
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
                'type'          => 'Segment',
                'options'       => [
                    'route' => '/[:language/]fichier',
                    'defaults'      => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'FichierThese',
                        'language'   => 'fr_FR',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'deposes' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/deposes',
                            'defaults'      => [
                                'action' => 'deposes',
                            ],
                        ],
                    ],
                    'these' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/these/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'lister-fichiers'     => [
                                'type'     => 'Segment',
                                'options'  => [
                                    'route' => '/lister-fichiers',
                                    'defaults' => [
                                        'action' => 'lister-fichiers',
                                        /* @see FichierTheseController::listerFichiersAction() */
                                    ],
                                ],
                            ],
                            'televerser'  => [
                                'type'     => 'Segment',
                                'options'  => [
                                    'route' => '/televerser',
                                    'defaults' => [
                                        'action' => 'televerser-fichier',
                                        /* @see FichierTheseController::televerserFichierAction() */
                                    ],
                                ],
                            ],
                            'telecharger' => [
                                'type'        => 'Segment',
                                'options'     => [
                                    'route' => '/telecharger/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults'    => [
                                        'action' => 'telecharger-fichier',
                                        /* @see FichierTheseController::telechargerFichierAction() */
                                    ],
                                ],
                            ],
                            'apercevoir' => [
                                'type'        => 'Segment',
                                'options'     => [
                                    'route' => '/apercevoir/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults'    => [
                                        'action' => 'apercevoir-fichier',
                                    ],
                                ],
                            ],
                            'apercevoir-page-de-couverture' => [
                                'type'        => 'Segment',
                                'options'     => [
                                    'route' => '/apercevoir-page-de-couverture',
                                    'defaults'    => [
                                        'action' => 'apercevoir-page-de-couverture',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'        => 'Segment',
                                'options'     => [
                                    'route' => '/supprimer/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults'    => [
                                        'action' => 'supprimer-fichier',
                                    ],
                                ],
                            ],
                            'recuperer-fusion' => [
                                'type'        => 'Segment',
                                'options'     => [
                                    'route' => '/recuperer-fusion/:outputFile',
                                    'defaults'    => [
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
                        'route'    => 'fichier fusionner --these= --versionFichier= [--removeFirstPage] [--notifier=]',
                        'defaults' => [
                            'controller' => 'Application\Controller\FichierThese',
                            'action'     => 'fusionnerConsole',
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
                            'fichiers-deposes' => [
                                'label'    => 'Fichiers de thèses',
                                'route'    => 'fichier/deposes',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId('Application\Controller\FichierThese', 'deposes'),
                            ],
                            'rapport-annuel' => [
                                'label'    => 'Rapport annuel',
                                'route'    => 'fichier/deposes',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId('Application\Controller\FichierThese', 'deposes'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'FichierTheseService' => FichierTheseServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'Application\Controller\FichierThese' => FichierTheseControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichierThese'       => UrlFichierThese::class,
        ],
    ],
];
