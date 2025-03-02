<?php

namespace Application;

use Application\Controller\Factory\ListeDiffusionControllerFactory;
use Application\Controller\ListeDiffusionController;
use Application\Provider\Privilege\ListeDiffusionPrivileges;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\ListeDiffusionServiceFactory;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandler;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandlerFactory;
use Application\Service\ListeDiffusion\Url\UrlService;
use Application\Service\ListeDiffusion\Url\UrlServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ListeDiffusionController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => ListeDiffusionPrivileges::LISTE_DIFFUSION_LISTER,
                ],
                [
                    'controller' => ListeDiffusionController::class,
                    'action' => [
                        'consulter',
                        'exporter-tableau',
                    ],
                    'privileges' => ListeDiffusionPrivileges::LISTE_DIFFUSION_CONSULTER,
                ],
                [
                    'controller' => ListeDiffusionController::class,
                    'action' => [
                        'generate-member-include',
                        'generate-owner-include',
                    ],
                    'roles' => [], // doit être accessible librement
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'liste-diffusion' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/liste-diffusion',
                    'defaults' => [
                        'controller' => ListeDiffusionController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'liste' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:adresse',
                            'constraints' => [
                                'adresse' => '[a-zA-Z0-9-_.@]+',
                            ],
                            'defaults' => [
                                /** @see ListeDiffusionController::consulterAction() */
                                'action' => 'consulter',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'generate-member-include' => [
                                'type' => Segment::class,
                                'options' => [
//                                    'route' => '/95c66625fe1e45a41e4a51d3f786210ab0a47479/generate-member-include/',
                                    'route' => '/:token/generate-member-include/',
                                    'defaults' => [
                                        /** @see ListeDiffusionController::generateMemberIncludeAction() */
                                        'action' => 'generate-member-include',
                                    ],
                                ],
                            ],
                            'generate-owner-include' => [
                                'type' => Segment::class,
                                'options' => [
//                                    'route' => '/ef35998526851f484a027478ef9b7e936a0366e5/generate-owner-include',
                                    'route' => '/:token/generate-owner-include',
                                    'defaults' => [
                                        /** @see ListeDiffusionController::generateOwnerIncludeAction() */
                                        'action' => 'generate-owner-include',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'export-tableau' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/exporter-tableau',
                            'defaults' => [
                                /** @see ListeDiffusionController::exporterTableauAction() */
                                'action' => 'exporter-tableau',
                            ],
                        ],
                    ]
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
                            '-----------mails-divider' => [
                                'label' => null,
                                'order' => 800,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'liste-diffusion' => [
                                'label'    => 'Listes de diffusion',
                                'route'    => 'liste-diffusion',
                                'icon'     => 'fas fa-envelope',
                                'order'    => 810,
                                'resource' => PrivilegeController::getResourceId(ListeDiffusionController::class, 'index'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            ListeDiffusionService::class => ListeDiffusionServiceFactory::class,
            ListeDiffusionHandler::class => ListeDiffusionHandlerFactory::class,
            UrlService::class => UrlServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ListeDiffusionController::class => ListeDiffusionControllerFactory::class,
        ],
    ],
];
