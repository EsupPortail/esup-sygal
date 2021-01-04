<?php

namespace UnicaenDeploy;

use UnicaenDeploy\Config\Config;
use UnicaenDeploy\Config\ConfigFactory;
use UnicaenDeploy\Controller\ConsoleController;
use UnicaenDeploy\Controller\ConsoleControllerFactory;
use UnicaenDeploy\Controller\IndexController;
use UnicaenDeploy\Controller\IndexControllerFactory;
use UnicaenDeploy\Domain\CopyTask;
use UnicaenDeploy\Domain\PushTask;
use UnicaenDeploy\Domain\RunTask;
use UnicaenDeploy\Service\DeployService;
use UnicaenDeploy\Service\DeployServiceFactory;
use Zend\Mvc\Console\Router\Simple;
use Zend\Router\Http\Literal;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'bjyauthorize'    => [
        'guards'                => [
            'BjyAuthorize\Guard\Controller'         => [
                ['controller' => IndexController::class, 'roles' => 'guest'],
                ['controller' => ConsoleController::class, 'roles' => 'guest'],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'deploy_home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/deploy',
                    'defaults' => [
                        /**
                         * @see IndexController::indexAction()
                         */
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'index' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'deploy --target=',
                        'defaults' => [
                            /**
                             * @see ConsoleController::deployAction()
                             */
                            'controller' => ConsoleController::class,
                            'action'     => 'deploy',
                        ],
                    ],
                ],
            ],
        ],
        'view_manager' => [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
        ]
    ],
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            Config::class => ConfigFactory::class,

            DeployService::class => DeployServiceFactory::class,

            PushTask::class => InvokableFactory::class,
            CopyTask::class => InvokableFactory::class,
            RunTask::class => InvokableFactory::class,
        ],
    ],
    'translator'      => [
        'locale'                    => 'fr_FR', // en_US
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
            ConsoleController::class => ConsoleControllerFactory::class,
        ],
    ],
    'view_manager'    => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
