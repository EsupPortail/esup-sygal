<?php

use Application\Command\CheckWSValidationFichierCinesCommandFactory;
use Application\Command\ValidationFichierCinesCommandFactory;
use Application\Controller\Factory\FichierControllerFactory;
use Application\Controller\Factory\FichierTheseControllerFactory;
use Application\Controller\FichierTheseController;
use Application\Controller\Plugin\UrlFichier;
use Application\Controller\Plugin\UrlFichierThese;
use Application\Provider\Privilege\FichierPrivileges;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Fichier\FichierService;
use Application\Service\Fichier\FichierServiceFactory;
use Application\Service\FichierThese\FichierTheseServiceFactory;
use Application\Service\File\FileService;
use Application\Service\File\FileServiceFactory;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Fichier' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                            FichierPrivileges::FICHIER_COMMUN_TELEVERSER,
                        ],
                        'resources'  => ['Fichier'],
                        //'assertion'  => 'Assertion\\These',
                    ],
                ],
            ],
        ],
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

                [
                    'controller' => 'Application\Controller\Fichier',
                    'action'     => [
                        'lister-fichiers-communs',
                    ],
                    'privileges' => FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                ],

                [
                    'controller' => 'Application\Controller\Fichier',
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Fichier',
                    'action'     => [
                        'televerser-fichiers-communs',
                        'supprimer',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELEVERSER,
                    ],
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

                    /*--------------- Thèse --------------*/
                    'these' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/these/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults'      => [
//                                'controller' => 'These',
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

                    /*--------------- Thèse --------------*/
                    'deposes' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/deposes',
                            'defaults'      => [
                                'action' => 'deposes',
                            ],
                        ],
                    ],

                    /*--------------- Hors Thèse --------------*/
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults'      => [
                                'controller' => 'Fichier',
                                'action' => 'telecharger',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/supprimer/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults'      => [
                                'controller' => 'Fichier',
                                'action' => 'supprimer',
                            ],
                        ],
                    ],

                    'televerser-fichiers-communs' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/televerser-fichiers-communs',
                            'defaults'      => [
                                'controller' => 'Fichier',
                                'action' => 'televerser-fichiers-communs',
                            ],
                        ],
                    ],
                    'lister-fichiers-communs' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/lister-fichiers-communs',
                            'defaults'      => [
                                'controller' => 'Fichier',
                                'action' => 'lister-fichiers-communs',
                            ],
                        ],
                    ],
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
                            'fichiers-communs' => [
                                'label'    => 'Fichiers communs',
                                'route'    => 'fichier/lister-fichiers-communs',
                                'order'    => 200,
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Fichier', 'lister-fichiers-communs'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'VersionFichierService' => VersionFichierService::class,
            'NatureFichierService' => NatureFichierService::class,
            'ValiditeFichierService' => ValiditeFichierService::class,
        ],
        'factories' => [
            FileService::class => FileServiceFactory::class,
            FichierService::class => FichierServiceFactory::class,
            'FichierTheseService' => FichierTheseServiceFactory::class,
            'ValidationFichierCinesCommand' => ValidationFichierCinesCommandFactory::class,
            'CheckWSValidationFichierCinesCommand' => CheckWSValidationFichierCinesCommandFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'Application\Controller\Fichier' => FichierControllerFactory::class,
            'Application\Controller\FichierThese' => FichierTheseControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichier'            => UrlFichier::class,
            'urlFichierThese'       => UrlFichierThese::class,
        ],
    ],
];
