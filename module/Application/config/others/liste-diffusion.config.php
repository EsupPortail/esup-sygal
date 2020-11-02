<?php

namespace Application;

use Application\Controller\Factory\ListeDiffusionControllerFactory;
use Application\Controller\ListeDiffusionController;
use Application\Provider\Privilege\ListeDiffusionPrivileges;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\ListeDiffusionServiceFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionRolePlugin;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionRolePluginFactory;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionStructurePlugin;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionStructurePluginFactory;
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
