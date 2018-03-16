<?php

use Application\Command\CheckWSValidationFichierCinesCommandFactory;
use Application\Command\ValidationFichierCinesCommandFactory;
use Application\Controller\Factory\FichierControllerFactory;
use Application\Controller\Factory\FichierTheseControllerFactory;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Fichier\FichierServiceFactory;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use UnicaenAuth\Guard\PrivilegeController;

return array(
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
                        'lister',
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
            ],
        ],
    ],

    'router' => array(
        'routes' => array(

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
                            'lister'     => [
                                'type'     => 'Segment',
                                'options'  => [
                                    'route' => '/lister',
                                    'defaults' => [
                                        'action' => 'lister',
                                    ],
                                ],
                            ],
                            'lister-fichiers'     => [
                                'type'     => 'Segment',
                                'options'  => [
                                    'route' => '/lister-fichiers',
                                    'defaults' => [
                                        'action' => 'lister-fichiers',
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
                ],
            ], // 'fichier'

        ),
    ),
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'fichiers-deposes' => [
                                'label'    => 'Fichiers déposés',
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
    'service_manager' => array(
        'invokables' => array(
            'VersionFichierService' => VersionFichierService::class,
            'ValiditeFichierService' => ValiditeFichierService::class,
        ),
        'factories' => array(
            'FichierService' => FichierServiceFactory::class,
            'ValidationFichierCinesCommand' => ValidationFichierCinesCommandFactory::class,
            'CheckWSValidationFichierCinesCommand' => CheckWSValidationFichierCinesCommandFactory::class,
        ),
    ),
    'controllers' => [
        'factories' => [
            'Application\Controller\Fichier' => FichierControllerFactory::class,
            'Application\Controller\FichierThese' => FichierTheseControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichierThese'       => 'Application\Controller\Plugin\UrlFichierThese',
        ],
    ],
);
