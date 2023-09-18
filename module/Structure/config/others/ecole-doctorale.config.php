<?php

use Structure\Controller\EcoleDoctoraleController;
use Structure\Controller\Factory\EcoleDoctoraleControllerFactory;
use Structure\Form\Factory\EcoleDoctoraleFormFactory;
use Structure\Form\Factory\EcoleDoctoraleHydratorFactory;
use Structure\Provider\Privilege\StructurePrivileges;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceFactory;
use Structure\View\Helper\EcoleDoctoraleHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EcoleDoctoraleController::class,
                    'action'     => [
                        'rechercher'
                    ],
                    'role' => [],
                ],
                [
                    'controller' => EcoleDoctoraleController::class,
                    'action'     => [
                        'index',
                        'voir',
                        'information',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
                ],
                [
                    'controller' => EcoleDoctoraleController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                        'restaurer',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CREATION_ED,
                    ],
                ],
                [
                    'controller' => EcoleDoctoraleController::class,
                    'action'     => [
                        'modifier',
                        'ajouter-individu',
                        'retirer-individu',
                        'supprimer-logo',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                    ],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'ecole-doctorale' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/ecole-doctorale',
                    'defaults' => [
                        'controller'    => EcoleDoctoraleController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'rechercher' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/rechercher',
                            'defaults'    => [
                                'action' => 'rechercher',
                            ],
                        ],
                    ],
                    'voir' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/voir/:ecole-doctorale',
                            'defaults' => [
                                'action' => 'voir',
                            ],
                        ],
                    ],
                    'information' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/information/:structure',
                            'defaults'    => [
                                'action' => 'information',
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
                            'route'       => '/supprimer/:structure',
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/restaurer/:structure',
                            'defaults'    => [
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/modifier/:structure',
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'supprimer-logo' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer-logo/:structure',
                            'defaults'    => [
                                'action' => 'supprimer-logo',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'ecole-doctorale' => [
                                'label'    => 'Écoles doctorales',
                                'route'    => 'ecole-doctorale',
                                'resource' => PrivilegeController::getResourceId(EcoleDoctoraleController::class, 'index'),

                                'order'    => 11,
                                'pages' => [
                                    'modification' => [
                                        'label'    => 'Modification',
                                        'route'    => 'ecole-doctorale/modifier',
                                        'resource' => PrivilegeController::getResourceId(EcoleDoctoraleController::class, 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'structure',
                                        ],
                                    ],
                                    'information' => [
                                        'label'    => 'Détails',
                                        'route'    => 'ecole-doctorale/information',
                                        'resource' => PrivilegeController::getResourceId(EcoleDoctoraleController::class, 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'structure',
                                        ],
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
        'invokables' => [
        ],
        'factories' => [
            'EcoleDoctoraleService' => EcoleDoctoraleServiceFactory::class,
        ],
        'aliases' => [
            EcoleDoctoraleService::class => 'EcoleDoctoraleService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Structure\Controller\EcoleDoctorale' => EcoleDoctoraleControllerFactory::class,
        ],
        'aliases' => [
            EcoleDoctoraleController::class => 'Structure\Controller\EcoleDoctorale',
        ]
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'EcoleDoctoraleForm' => EcoleDoctoraleFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            'EcoleDoctoraleHydrator' => EcoleDoctoraleHydratorFactory::class,
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'ed' => EcoleDoctoraleHelper::class,
        ],
    ],
];
