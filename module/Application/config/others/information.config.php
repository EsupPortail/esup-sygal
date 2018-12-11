<?php

use Application\Controller\Factory\InformationControllerFactory;
use Application\Controller\InformationController;
use Application\Form\Factory\InformationFormFactory;
use Application\Form\Hydrator\InformationHydrator;
use Application\Form\InformationForm;
use Application\Service\Information\InformationService;
use Application\Service\Information\InformationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'index',
                    ],
//                    'privileges' => [
//                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
//                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
//                    ],
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                    ],
//                    'privileges' => [
//                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
//                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
//                        StructurePrivileges::STRUCTURE_CREATION_ED,
//                    ],
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'modifier',
                    ],
//                    'privileges' => [
//                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
//                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
//                    ],
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'afficher',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'informations' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/informations',
                    'defaults' => [
                        'controller'    => InformationController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'afficher' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/afficher/:id',
                            'defaults'    => [
                                'action' => 'afficher',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer/:id',
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/modifier/:id',
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            InformationService::class => InformationServiceFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            InformationController::class => InformationControllerFactory::class
        ],
    ],
    'form_elements'   => [
        'factories' => [
            InformationForm::class => InformationFormFactory::class,
        ],
    ],
    'hydrators' => [
        'invokables' => [
            InformationHydrator::class => InformationHydrator::class
        ]
    ],
    'view_helpers' => [
        'invokables' => [
        ],
    ],
];
