<?php

use Structure\Controller\EtablissementController;
use Structure\Controller\Factory\EtablissementControllerFactory;
use Structure\Form\Factory\EtablissementFormFactory;
use Structure\Form\Factory\EtablissementHydratorFactory;
use Structure\Provider\Privilege\StructurePrivileges;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Etablissement\EtablissementServiceFactory;
use Structure\View\Helper\EtablissementHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EtablissementController::class,
                    'action'     => [
                        'rechercher',
                    ],
                    'role' => [],
                ],
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
                        'televerser-signature-convocation',
                        'supprimer-signature-convocation',
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
                    'rechercher' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/rechercher',
                            'defaults'    => [
                                'action' => 'rechercher',
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
                    'televerser-signature-convocation' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/televerser-signature-convocation/:structure',
                            'defaults'    => [
                                'action' => 'televerser-signature-convocation',
                            ],
                        ],
                    ],
                    'supprimer-signature-convocation' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer-signature-convocation/:structure',
                            'defaults'    => [
                                'action' => 'supprimer-signature-convocation',
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
                                'order'    => 10,
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
            'Structure\Controller\Etablissement' => EtablissementControllerFactory::class,
        ],
        'aliases' => [
            EtablissementController::class => 'Structure\Controller\Etablissement',
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
