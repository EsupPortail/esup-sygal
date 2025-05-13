<?php

/**
 * Config concernant les fichiers NON liés à une thèse.
 */

namespace Fichier;

use Fichier\Command\TestArchivabiliteShellCommandFactory;
use Fichier\Controller\ConsoleController;
use Fichier\Controller\Factory\FichierControllerFactory;
use Fichier\Controller\FichierController;
use Fichier\Controller\Plugin\UrlFichier;
use Fichier\Exporter\PageFichierIntrouvablePdfExporter;
use Fichier\Exporter\PageFichierIntrouvablePdfExporterFactory;
use Fichier\Form\FichierForm;
use Fichier\Form\FichierFormFactory;
use Fichier\Hydrator\FichierHydrator;
use Fichier\Hydrator\FichierHydratorFactory;
use Fichier\Provider\Privilege\FichierPrivileges;
use Fichier\Search\NatureFichierSearchFilter;
use Fichier\Search\NatureFichierSearchFilterFactory;
use Fichier\Search\NomFichierSearchFilter;
use Fichier\Search\VersionFichierSearchFilter;
use Fichier\Search\VersionFichierSearchFilterFactory;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierServiceFactory;
use Fichier\Service\Fichier\Search\FichierSearchService;
use Fichier\Service\Fichier\Search\FichierSearchServiceFactory;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Fichier\View\Helper\Fichier\FichierViewHelper;
use Laminas\ServiceManager\Factory\InvokableFactory;
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
                    'controller' => FichierController::class,
                    'action' => [
                        'index',
                        'lister-fichiers-communs',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                    ],
                ],
                [
                    'controller' => FichierController::class,
                    'action' => [
                        'telecharger',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_COMMUN_TELECHARGER,
                    ],
                ],
                [
                    'controller' => FichierController::class,
                    'action' => [
                        'telecharger-permanent',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => FichierController::class,
                    'action' => [
                        'televerser-fichiers-communs',
                        'afficher',
                        'modifier',
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
                        'controller' => FichierController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'telecharger' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/telecharger/:fichier[/:fichierNom]',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::telechargerAction() */
                                'action' => 'telecharger',
                            ],
                        ],
                    ],
                    'telecharger-permanent' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/telecharger/permanent/:idPermanent',
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::telechargerPermanentAction() */
                                'action' => 'telecharger-permanent',
                            ],
                        ],
                    ],
                    'afficher' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/afficher/:fichier',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::afficherAction() */
                                'action' => 'afficher',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier/:fichier',
                            'constraints' => [
                                'fichier' => '[a-zA-Z0-9-]+',
                            ],
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::modifierAction() */
                                'action' => 'modifier',
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
                                /** @see \Fichier\Controller\FichierController::supprimerAction() */
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'televerser-fichiers-communs' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/televerser-fichiers-communs',
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::televerserFichiersCommunsAction() */
                                'action' => 'televerser-fichiers-communs',
                            ],
                        ],
                    ],
                    'lister-fichiers-communs' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/lister-fichiers-communs',
                            'defaults' => [
                                /** @see \Fichier\Controller\FichierController::listerFichiersCommunsAction() */
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
                            'fichiers' => [
                                'label' => 'Fichiers',
                                'route' => 'fichier',
                                'order' => 598,
                                'resource' => PrivilegeController::getResourceId(FichierController::class, 'index'),
                                'icon' => 'fas fa-file'
                            ],
                            'fichiers-communs' => [ ///////////// todo : à supprimer lorsqu'on pourra créer/uploader des fichiers depuis "FichierController::index".
                                'label' => 'Fichiers communs',
                                'route' => 'fichier/lister-fichiers-communs',
                                'order' => 599,
                                'resource' => PrivilegeController::getResourceId(FichierController::class, 'lister-fichiers-communs'),
                                'icon' => 'fas fa-file'
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
            FichierController::class => FichierControllerFactory::class,
        ],
        'aliases' => [
            'Application\Controller\Fichier' => FichierController::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            FichierForm::class => FichierFormFactory::class,
        ],
    ] ,
    'controller_plugins' => [
        'invokables' => [
            'urlFichier' => UrlFichier::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            FichierHydrator::class => FichierHydratorFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'fichier' => FichierViewHelper::class,
        ],
    ],
    'search_services' => [
        'factories' => [
            FichierSearchService::class => FichierSearchServiceFactory::class,
        ],
    ],
    'search_filters' => [
        'factories' => [
            NatureFichierSearchFilter::class => NatureFichierSearchFilterFactory::class,
            VersionFichierSearchFilter::class => VersionFichierSearchFilterFactory::class,
            NomFichierSearchFilter::class => InvokableFactory::class,
        ],
    ],
];
