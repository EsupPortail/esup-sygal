<?php

use Application\Controller\Factory\UniteRechercheControllerFactory;
use Application\Controller\UniteRechercheController;
use Application\Form\Factory\UniteRechercheFormFactory;
use Application\Form\Factory\UniteRechercheHydratorFactory;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Service\DomaineScientifiqueService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UniteRecherche\UniteRechercheServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Application\View\Helper\UniteRechercheHelper;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => UniteRechercheController::class,
                    'action'     => [
                        'rechercher',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => UniteRechercheController::class,
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
                    'controller' => UniteRechercheController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                        'restaurer',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_MODIFICATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_MODIFICATION_SES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CREATION_UR,
                    ],
                ],
                [
                    'controller' => UniteRechercheController::class,
                    'action'     => [
                        'modifier',
                        'supprimer-logo',
                        'ajouter-etablissement-rattachement',
                        'retirer-etablissement-rattachement',
                        'ajouter-domaine-scientifique',
                        'retirer-domaine-scientifique',
                        'principal-etablissement-rattachement',
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
            'unite-recherche' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/unite-recherche',
                    'defaults' => [
                        'controller'    => UniteRechercheController::class,
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
                            'route'       => '/modifier[/:structure]',
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ajouter-etablissement-rattachement' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'       => '/ajouter-etablissement-rattachement/:etablissement',
                                    'defaults'    => [
                                        'action' => 'ajouter-etablissement-rattachement',
                                    ],
                                ],
                            ],
                            'retirer-etablissement-rattachement' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'       => '/retirer-etablissement-rattachement/:etablissement',
                                    'defaults'    => [
                                        'action' => 'retirer-etablissement-rattachement',
                                    ],
                                ],
                            ],
                            'principal-etablissement-rattachement' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'       => '/principal-etablissement-rattachement/:etablissement',
                                    'defaults'    => [
                                        'action' => 'principal-etablissement-rattachement',
                                    ],
                                ],
                            ],
                            'ajouter-domaine-scientifique' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'       => '/ajouter-domaine-scientifique/:domaineScientifique',
                                    'defaults'    => [
                                        'action' => 'ajouter-domaine-scientifique',
                                    ],
                                ],
                            ],
                            'retirer-domaine-scientifique' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'       => '/retirer-domaine-scientifique/:domaineScientifique',
                                    'defaults'    => [
                                        'action' => 'retirer-domaine-scientifique',
                                    ],
                                ],
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
                            'unite-recherche' => [
                                'label'    => 'Unités de recherche',
                                'route'    => 'unite-recherche',
                                'resource' => PrivilegeController::getResourceId(UniteRechercheController::class, 'index'),

                                'order'    => 12,
                                'pages' => [
                                    'modification' => [
                                        'label'    => 'Modification',
                                        'route'    => 'unite-recherche/modifier',
                                        'resource' => PrivilegeController::getResourceId(UniteRechercheController::class, 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'structure',
                                        ],
                                    ],
                                    'information' => [
                                        'label'    => 'Détails',
                                        'route'    => 'unite-recherche/information',
                                        'resource' => PrivilegeController::getResourceId(UniteRechercheController::class, 'index'),

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
            DomaineScientifiqueService::class => DomaineScientifiqueService::class,
        ],
        'factories' => [
            'UniteRechercheService' => UniteRechercheServiceFactory::class,
        ],
        'aliases' => [
            UniteRechercheService::class => 'UniteRechercheService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\UniteRecherche' => UniteRechercheControllerFactory::class,
        ],
        'aliases' => [
            UniteRechercheController::class => 'Application\Controller\UniteRecherche',
        ]
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'UniteRechercheForm' => UniteRechercheFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            'UniteRechercheHydrator' => UniteRechercheHydratorFactory::class,
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'ur' => UniteRechercheHelper::class,
        ],
    ],
];
