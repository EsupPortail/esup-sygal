<?php

namespace Application;

use Application\Controller\Factory\ListeDiffusionControllerFactory;
use Application\Controller\ListeDiffusionController;
use Application\Provider\Privilege\ListeDiffusionPrivileges;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\ListeDiffusionServiceFactory;
use Application\Service\ListeDiffusion\Plugin\Structure\ListeDiffusionEcoleDoctoralePlugin;
use Application\Service\ListeDiffusion\Plugin\Structure\ListeDiffusionEcoleDoctoralePluginFactory;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandler;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandlerFactory;
use Application\Service\ListeDiffusion\Plugin\Structure\ListeDiffusionUniteRecherchePlugin;
use Application\Service\ListeDiffusion\Plugin\Structure\ListeDiffusionUniteRecherchePluginFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

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
                            // exemple: /consulter/ed591.doctorants.ucn@normandie-univ.fr
                            // exemple: /consulter/ed591.doctorants@normandie-univ.fr
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
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/95c66625fe1e45a41e4a51d3f786210ab0a47479/generate-member-include/',
                                    'defaults' => [
                                        /** @see ListeDiffusionController::generateMemberIncludeAction() */
                                        'action' => 'generate-member-include',
                                    ],
                                ],
                            ],
                            'generate-owner-include' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ef35998526851f484a027478ef9b7e936a0366e5/generate-owner-include',
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
                            'liste-diffusion' => [
                                'label'    => 'Listes de diffusion',
                                'route'    => 'liste-diffusion',
                                'icon'     => 'icon icon-notifier',
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
        ],
    ],
    'controllers' => [
        'factories' => [
            ListeDiffusionController::class => ListeDiffusionControllerFactory::class,
        ],
    ],
];
