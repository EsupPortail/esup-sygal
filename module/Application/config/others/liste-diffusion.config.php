<?php

namespace Application;

use Application\Controller\Factory\ListeDiffusionControllerFactory;
use Application\Controller\ListeDiffusionController;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\ListeDiffusionServiceFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionRolePlugin;
use Application\Service\ListeDiffusion\ListeDiffusionRoleServiceFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionRolePluginFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionStructurePlugin;
use Application\Service\ListeDiffusion\ListeDiffusionStructureServiceFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionStructurePluginFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ListeDiffusionController::class,
                    'action' => [
                        'index',
                        'consulter',
                        'generate-member-include',
                        'generate-owner-include',
                    ],
                    'roles' => [],
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
                            'route' => '/:liste',
                            // exemple: /consulter/ed591.doctorants.ucn@normandie-univ.fr
                            // exemple: /consulter/ed591.doctorants@normandie-univ.fr
                            'constraints' => [
                                'liste' => '[a-zA-Z0-9-_.@]+',
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
                                    'route' => '/generate-member-include',
                                    'defaults' => [
                                        /** @see ListeDiffusionController::generateMemberIncludeAction() */
                                        'action' => 'generate-member-include',
                                    ],
                                ],
                            ],
                            'generate-owner-include' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/generate-owner-include',
                                    'defaults' => [
                                        /** @see ListeDiffusionController::generateOwnerIncludeAction() */
                                        'action' => 'generate-owner-include',
                                    ],
                                ],
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
            ListeDiffusionStructurePlugin::class => ListeDiffusionStructurePluginFactory::class,
            ListeDiffusionRolePlugin::class => ListeDiffusionRolePluginFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ListeDiffusionController::class => ListeDiffusionControllerFactory::class,
        ],
    ],
];
