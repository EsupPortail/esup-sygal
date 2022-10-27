<?php

namespace Application;

use Application\Assertion\AssertionAbstractFactory;
use Application\Cache\MemcachedFactory;
use Application\Controller\Factory\IndexControllerFactory;
use Application\Controller\Plugin\Forward;
use Application\Controller\Plugin\ForwardFactory;
use Application\ORM\Query\Functions\ToNumber;
use Fichier\Controller\Plugin\Uploader\UploaderPluginFactory;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\UserWrapperFactory;
use Application\Entity\UserWrapperFactoryFactory;
use Application\Event\UserAuthenticatedEventListenerFactory;
use Application\Event\UserRoleSelectedEventListener;
use Application\Navigation\NavigationFactoryFactory;
use Application\ORM\Query\Functions\StrReduce;
use Application\Service\AuthorizeServiceAwareInitializer;
use Application\Service\Role\RoleService;
use Application\Service\Role\RoleServiceFactory;
use Application\Service\ServiceAwareInitializer;
use Application\Service\Url\UrlServiceFactory;
use Application\Service\UserContextService;
use Application\Service\UserContextServiceAwareInitializer;
use Application\Service\UserContextServiceFactory;
use Application\View\Helper\EscapeTextHelper;
use Application\View\Helper\FiltersPanel\FiltersPanelHelper;
use Application\View\Helper\Sortable;
use Application\View\Helper\SortableHelperFactory;
use Fichier\View\Helper\Uploader\UploaderHelper;
use Fichier\View\Helper\Uploader\UploaderHelperFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver as PgSQL;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Laminas\Navigation\Navigation;
use Structure\Form\Factory\EcoleDoctoraleFormFactory;
use UnicaenApp\Controller\ConsoleController;
use UnicaenApp\Service\EntityManagerAwareInitializer;

return array(
    'bjyauthorize' => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                // la page Contact requiert une authentification car l'adresse d'assistance dépend de l'utilisateur
                ['controller' => 'UnicaenApp\Controller\Application', 'action' => 'contact', 'roles' => ['user']],
                ['controller' => 'Application\Controller\Index', 'action' => 'contact', 'roles' => ['user']],
                ['controller' => ConsoleController::class, 'action' => 'runSQLScript', 'roles' => []],
                ['controller' => ConsoleController::class, 'action' => 'runSQLQuery', 'roles' => []],
            ],
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => \UnicaenOracle\Controller\IndexController::class,
                    'action'     => [
                        'generateScriptForSchemaClearingConsole',
                        'generateScriptForSchemaCreationConsole',
                        'generateScriptForRefConstraintsCreationConsole',
                        'generateScriptsForDataInsertsConsole',
                    ],
                    'roles' => [], // pas d'authentification requise
                ],
                [
                    'controller' => 'DoctrineModule\Controller\Cli',
                    'roles' => [],
                ],

            ],
        ],
    ],
    'doctrine'     => [
        /**
         * Génération du mapping à partir de la bdd, exemple :
         *   $ vendor/bin/doctrine-module orm:convert-mapping --namespace="Application\\Entity\\Db\\" --filter="Version" --from-database xml module/Application/src/Application/Entity/Db/Mapping
         *
         * Génération des classes d'entité, exemple :
         *   $ vendor/bin/doctrine-module orm:generate:entities --filter="Version" module/Application/src
         */
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Application\Entity\Db' => 'orm_default_xml_driver',
                    'Application\Entity\Db\VSitu' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Application/Entity/Db/Mapping',
                    __DIR__ . '/../src/Application/Entity/Db/Mapping/VSitu',
                ],
            ],
        ],
        'eventmanager'  => [
            'orm_default' => [
                'subscribers' => [
                    'UnicaenApp\HistoriqueListener',
                ],
            ],
        ],
        'connection'    => [
            'orm_default' => [
                'driver_class' => PgSQL::class,
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'default_repository_class_name' => DefaultEntityRepository::class,
                'string_functions' => [
                    'strReduce' => StrReduce::class,
                    'toNumber' => ToNumber::class,
                ],
            ]
        ],
        'cache' => [
            'memcached' => [
                'namespace' => 'Sygal_Doctrine',
                'instance'  => 'Sygal\Memcached',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            'contact'          => [
//                'type'     => 'Laminas\Router\Http\Literal',
                'options'  => [
//                    'route'    => '/contact',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
//                        'action'     => 'contact',
                    ],
                ],
                'priority' => 9999,
            ],
            /*'lhome' => [
                'type' => 'Segment',
                'options' => [
                    'route'    => '/:language',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                        'language'   => 'fr_FR',
                    ],
                    'may_terminate' => true,
                ],
                'child_routes'  => [
                    'lthese' => [
                        'type' => 'Literal',
                        'options'       => [
                            'route'    => '/these',
                            'defaults' => [
                                'controller'    => TheseController::class',
                                'action'        => 'index',
                            ],
                        ],
                        'may_terminate' => true,

                        'child_routes'  => [
                            'lidentite' => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'       => '/identite/:these',
                                    'constraints' => [
                                        'these' => '\d+',
                                    ],
                                    'defaults'    => [
                                        'controller'    => TheseController::class,
                                        'action' => 'detail-identite',
                                    ],
                                ],
                            ],
                            'lrechercher' => [
                                'type'          => 'Segment',
                                'options'       => [
                                    'route'       => '/rechercher',
                                    'defaults'    => [
                                        'controller'    => TheseController::class,
                                        'action' => 'rechercher',
                                    ],
                                ],
                            ],
                        ],
                    ],

                ],
            ],*/
            'not-allowed' => [
                'type' => 'Literal',
                'options' => [
                    'route'    => '/not-allowed',
                    'defaults' => [
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'not-allowed',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => array(
        'aliases' => array(
            'UserContextService' => 'UnicaenAuth\Service\UserContext',
            RoleService::class => 'RoleService',
            UserContextService::class => 'UserContextService',
        ),
        'invokables' => array(
            'RouteMatchInjector' => RouteMatchInjector::class,
            'UserRoleSelectedEventListener' => UserRoleSelectedEventListener::class,
        ),
        'factories' => array(
            Navigation::class => NavigationFactoryFactory::class,
            'UnicaenAuth\Service\UserContext' => UserContextServiceFactory::class,
            'UserAuthenticatedEventListener' => UserAuthenticatedEventListenerFactory::class,
            'Sygal\Memcached'                => MemcachedFactory::class,
            'RoleService' => RoleServiceFactory::class,
            SourceCodeStringHelper::class => SourceCodeStringHelperFactory::class,
            UserWrapperFactory::class => UserWrapperFactoryFactory::class,
        ),
        'abstract_factories' => [
            AssertionAbstractFactory::class,
            UrlServiceFactory::class, // construit: 'urlTheseService'
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
            AuthorizeServiceAwareInitializer::class,
            UserContextServiceAwareInitializer::class,
        ]
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => [
        'factories' => [
            'Application\Controller\Index' => IndexControllerFactory::class,
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            Forward::class => ForwardFactory::class,
            'uploader' => UploaderPluginFactory::class,
        ],
        'aliases' => [
            'forward' => Forward::class,
            'Uploader' => 'uploader',
        ],
        'initializers' => [
            EntityManagerAwareInitializer::class,
        ],
    ],
    'view_manager' => array(
        'template_map'             => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'filterPanel' => 'Application\View\Helper\FilterPanel\FilterPanelHelper',
            'filtersPanel' => FiltersPanelHelper::class,
            'escapeText'  => EscapeTextHelper::class,
        ),
        'factories' => array(
            'languageSelector'          => 'Application\View\Helper\LanguageSelectorHelperFactory',
            Sortable::class => SortableHelperFactory::class,
            UploaderHelper::class => UploaderHelperFactory::class,
        ),
        'aliases' => [
            'sortable' => Sortable::class,
            'uploader' => UploaderHelper::class,
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
        ],
    ),
    'form_elements'   => [
        'invokables'   => [
            'UploadForm' => 'Fichier\Controller\Plugin\Uploader\UploadForm',
        ],
        'factories' => [
            'EcoleDoctoraleForm' => EcoleDoctoraleFormFactory::class,
        ],
//        'initializers' => [
//            'UnicaenApp\Service\EntityManagerAwareInitializer',
//        ],
    ],
    'public_files' => [
        'head_scripts' => [
            '060_uploader' => "/vendor/jquery.ui.widget.js",
            '061_uploader' => "/vendor/jquery.iframe-transport.js",
            '062_uploader' => "/vendor/jquery.fileupload.js",
            '063_uploader' => "/vendor/unicaen.uploader.widget.js",
        ],
        'inline_scripts' => [
            '070_bootstrap-select' => '/vendor/bootstrap-select-1.14.0-beta3/js/bootstrap-select.min.js',
            '070_bootstrap-select-fr' => '/vendor/bootstrap-select-1.14.0-beta3/js/i18n/defaults-fr_FR.js',
        ],
        'stylesheets'           => [
            '050_bootstrap-theme' => false,
            '100_charte' => '/css/charte.css',
            '200_fa' => '/vendor/fontawesome-free-5.12.0-web/css/all.min.css',
            '300_bs' => '/vendor/bootstrap-select-1.14.0-beta3/css/bootstrap-select.min.css',
            '400_faa' => '/vendor/font-awesome-animation.min.css',
        ],
        'printable_stylesheets' => [
        ],
    ],
);