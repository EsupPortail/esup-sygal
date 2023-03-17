<?php

/**
 * Config concernant les fichiers NON liés à une thèse.
 */

namespace Fichier;

use Fichier\Command\TestArchivabiliteShellCommandFactory;
use Fichier\Controller\ConsoleController;
use Fichier\Controller\Factory\FichierControllerFactory;
use Fichier\Controller\Plugin\UrlFichier;
use Fichier\Exporter\PageFichierIntrouvablePdfExporter;
use Fichier\Exporter\PageFichierIntrouvablePdfExporterFactory;
use Fichier\Provider\Privilege\FichierPrivileges;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierServiceFactory;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Unicaen\Console\Router\Simple;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'fichier' => [
        // Page PDF de substitution en cas de fichier introuvable
        'page_fichier_introuvable' => [
            /** @see \Fichier\Exporter\PageFichierIntrouvablePdfExporterFactory */
            'template' => [
                // template .phtml
                'phtml_file_path' => __DIR__ . '/../../view/fichier/page-fichier-introuvable/template.phtml',
                // feuille de styles
                'css_file_path' => __DIR__ . '/../../view/fichier/page-fichier-introuvable/styles.css',
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'fichier:migrer-fichiers' => [
                    'type' => Simple::class,
                    'options' => [
                        'route' => 'fichier:migrer-fichiers --from= --to= [--verbose]',
                        'defaults' => [
                            /**
                             * @see \Fichier\Controller\ConsoleController::migrerFichiersAction()
                             */
                            'controller' => ConsoleController::class,
                            'action' => 'migrerFichiers',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Fichier' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                            FichierPrivileges::FICHIER_COMMUN_TELEVERSER,
                        ],
                        'resources' => ['Fichier'],
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    /**
                     * @see ConsoleController::migrerFichiersAction()
                     */
                    'controller' => ConsoleController::class,
                    'action' => [
                        'migrerFichiers',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => 'DoctrineModule\Controller\Cli',
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\Fichier',
                    'action' => [
                        'lister-fichiers-communs',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Fichier',
                    'action' => [
                        'telecharger',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Fichier',
                    'action' => [
                        'telecharger-permanent',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\Fichier',
                    'action' => [
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
                'type' => 'Segment',
                'options' => [
                    'route' => '/fichier',
                    'defaults' => [
                        'controller' => 'Application\Controller\Fichier',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'telecharger' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/telecharger/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults' => [
                                'action' => 'telecharger',
                            ],
                        ],
                    ],
                    'telecharger-permanent' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/telecharger/permanent/:idPermanent',
                            'defaults' => [
                                'action' => 'telecharger-permanent',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/supprimer/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults' => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'televerser-fichiers-communs' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/televerser-fichiers-communs',
                            'defaults' => [
                                'action' => 'televerser-fichiers-communs',
                            ],
                        ],
                    ],
                    'lister-fichiers-communs' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/lister-fichiers-communs',
                            'defaults' => [
                                'action' => 'lister-fichiers-communs',
                            ],
                        ],
                    ],
                ],
            ], // 'fichier'
        ],
    ],
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'fichiers-communs' => [
                                'label' => 'Fichiers communs',
                                'route' => 'fichier/lister-fichiers-communs',
                                'order' => 200,
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
            FichierService::class => FichierServiceFactory::class,
            PageFichierIntrouvablePdfExporter::class => PageFichierIntrouvablePdfExporterFactory::class,
            'ValidationFichierCinesCommand' => TestArchivabiliteShellCommandFactory::class,
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
            'urlFichier' => UrlFichier::class,
        ],
    ],
];
