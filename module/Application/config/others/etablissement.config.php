<?php

use Application\Controller\EtablissementController;
use Application\Controller\Factory\EtablissementControllerFactory;
use Application\Form\Factory\EtablissementFormFactory;
use Application\Form\Factory\EtablissementHydratorFactory;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Etablissement\EtablissementServiceFactory;
use Application\View\Helper\EtablissementHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EtablissementController::class,
                    'action'     => [
                        'index',
                        'information',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
                ],
                [
                    'controller' => EtablissementController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                        'restaurer',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CREATION_ETAB
                    ],
                ],
                [
                    'controller' => EtablissementController::class,
                    'action'     => [
                        'modifier',
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
            'etablissement' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/etablissement',
                    'defaults' => [
                        'controller'    => EtablissementController::class,
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
                            'constraints' => [
                                'etablissement' => '\d+',
                            ],
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
                            'etablissement' => [
                                'label'    => 'Établissements',
                                'route'    => 'etablissement',
                                'resource' => PrivilegeController::getResourceId(EtablissementController::class, 'index'),
                                'order'    => 5,
                                'pages' => [
                                    'modification' => [
                                        'label'    => 'Modification',
                                        'route'    => 'etablissement/modifier',
                                        'resource' => PrivilegeController::getResourceId(EtablissementController::class, 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'structure',
                                        ],
                                    ],
                                    'information' => [
                                        'label'    => 'Détails',
                                        'route'    => 'etablissement/information',
                                        'resource' => PrivilegeController::getResourceId(EtablissementController::class, 'index'),

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
            'EtablissementService' => EtablissementServiceFactory::class,
        ],
        'aliases' => [
            EtablissementService::class => 'EtablissementService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\Etablissement' => EtablissementControllerFactory::class,
        ],
        'aliases' => [
            EtablissementController::class => 'Application\Controller\Etablissement',
        ]
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'EtablissementForm' => EtablissementFormFactory::class,
        ],
    ],
    'hydrators' => array(
        'factories' => array(
            'EtablissementHydrator' => EtablissementHydratorFactory::class,
        )
    ),
    'view_helpers' => [
        'invokables' => [
            'etab' => EtablissementHelper::class,
        ],
    ],
];
