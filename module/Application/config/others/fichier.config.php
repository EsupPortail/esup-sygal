<?php

use Application\Command\CheckWSValidationFichierCinesCommandFactory;
use Application\Command\ValidationFichierCinesCommandFactory;
use Application\Controller\Factory\FichierControllerFactory;
use Application\Controller\Plugin\UrlFichier;
use Application\Provider\Privilege\FichierPrivileges;
use Application\Service\Fichier\FichierService;
use Application\Service\Fichier\FichierServiceFactory;
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
                        'telecharger-permanent',
                    ],
                    'roles' => [],
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
                        'controller' => 'Application\Controller\Fichier',
                        'language'   => 'fr_FR',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults'      => [
                                'action' => 'telecharger',
                            ],
                        ],
                    ],
                    'telecharger-permanent' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/permanent/:idPermanent',
                            'defaults'      => [
                                'action' => 'telecharger-permanent',
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
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'televerser-fichiers-communs' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/televerser-fichiers-communs',
                            'defaults'      => [
                                'action' => 'televerser-fichiers-communs',
                            ],
                        ],
                    ],
                    'lister-fichiers-communs' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/lister-fichiers-communs',
                            'defaults'      => [
                                'action' => 'lister-fichiers-communs',
                            ],
                        ],
                    ],
                ],
            ], // 'fichier'
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
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
            'ValidationFichierCinesCommand' => ValidationFichierCinesCommandFactory::class,
            'CheckWSValidationFichierCinesCommand' => CheckWSValidationFichierCinesCommandFactory::class,
        ],
        'aliases' => [
            'FichierService' => FichierService::class,
        ]
    ],
    'controllers' => [
        'factories' => [
            'Application\Controller\Fichier' => FichierControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichier'            => UrlFichier::class,
        ],
    ],
];
