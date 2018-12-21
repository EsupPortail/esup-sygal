<?php

use Application\Controller\EcoleDoctoraleController;
use Application\Controller\Factory\EcoleDoctoraleControllerFactory;
use Application\Form\Factory\EcoleDoctoraleFormFactory;
use Application\Form\Factory\EcoleDoctoraleHydratorFactory;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\View\Helper\EcoleDoctoraleHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EcoleDoctoraleController::class,
                    'action'     => [
                        'index',
                        'information'
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

                                'order'    => 10,
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
            'EcoleDoctoraleService' => EcoleDoctoraleService::class,
        ],
        'factories' => [
        ],
        'aliases' => [
            EcoleDoctoraleService::class => 'EcoleDoctoraleService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\EcoleDoctorale' => EcoleDoctoraleControllerFactory::class,
        ],
        'aliases' => [
            EcoleDoctoraleController::class => 'Application\Controller\EcoleDoctorale',
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
