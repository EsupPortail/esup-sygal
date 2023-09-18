<?php

namespace Substitution;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Substitution\Controller\DoublonController;
use Substitution\Controller\DoublonControllerFactory;
use Substitution\Controller\ForeignKeyController;
use Substitution\Controller\ForeignKeyControllerFactory;
use Substitution\Controller\IndexController;
use Substitution\Controller\IndexControllerFactory;
use Substitution\Controller\LogController;
use Substitution\Controller\LogControllerFactory;
use Substitution\Controller\SubstitutionController;
use Substitution\Controller\SubstitutionControllerFactory;
use Substitution\Service\DoublonService;
use Substitution\Service\DoublonServiceFactory;
use Substitution\Service\ForeignKeyService;
use Substitution\Service\ForeignKeyServiceFactory;
use Substitution\Service\LogService;
use Substitution\Service\LogServiceFactory;
use Substitution\Service\SubstitutionService;
use Substitution\Service\SubstitutionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'accueil',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => SubstitutionController::class,
                    'action' => [
                        'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => DoublonController::class,
                    'action' => [
                        'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => ForeignKeyController::class,
                    'action' => [
                        'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'role' => [],
                ],
                [
                    'controller' => LogController::class,
                    'action' => [
                        'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'role' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'substitution' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/substitution',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'accueil',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'substitution' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/substitution',
                            'defaults' => [
                                'controller' => SubstitutionController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'individu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/individu',
                                    'defaults' => [
                                        'action' => 'individu',
                                    ],
                                ],
                            ],
                            'doctorant' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/doctorant',
                                    'defaults' => [
                                        'action' => 'doctorant',
                                    ],
                                ],
                            ],
                            'structure' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/structure',
                                    'defaults' => [
                                        'action' => 'structure',
                                    ],
                                ],
                            ],
                            'etablissement' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/etablissement',
                                    'defaults' => [
                                        'action' => 'etablissement',
                                    ],
                                ],
                            ],
                            'ecole-doct' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ecole-doct',
                                    'defaults' => [
                                        'action' => 'ecole-doct',
                                    ],
                                ],
                            ],
                            'unite-rech' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/unite-rech',
                                    'defaults' => [
                                        'action' => 'unite-rech',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doublon' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/doublon',
                            'defaults' => [
                                'controller' => DoublonController::class,
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'individu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/individu',
                                    'defaults' => [
                                        'action' => 'individu',
                                    ],
                                ],
                            ],
                            'doctorant' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/doctorant',
                                    'defaults' => [
                                        'action' => 'doctorant',
                                    ],
                                ],
                            ],
                            'structure' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/structure',
                                    'defaults' => [
                                        'action' => 'structure',
                                    ],
                                ],
                            ],
                            'etablissement' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/etablissement',
                                    'defaults' => [
                                        'action' => 'etablissement',
                                    ],
                                ],
                            ],
                            'ecole-doct' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ecole-doct',
                                    'defaults' => [
                                        'action' => 'ecole-doct',
                                    ],
                                ],
                            ],
                            'unite-rech' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/unite-rech',
                                    'defaults' => [
                                        'action' => 'unite-rech',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'foreign-key' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/foreign-key',
                            'defaults' => [
                                'controller' => ForeignKeyController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'individu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/individu',
                                    'defaults' => [
                                        'action' => 'individu',
                                    ],
                                ],
                            ],
                            'doctorant' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/doctorant',
                                    'defaults' => [
                                        'action' => 'doctorant',
                                    ],
                                ],
                            ],
                            'structure' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/structure',
                                    'defaults' => [
                                        'action' => 'structure',
                                    ],
                                ],
                            ],
                            'etablissement' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/etablissement',
                                    'defaults' => [
                                        'action' => 'etablissement',
                                    ],
                                ],
                            ],
                            'ecole-doct' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ecole-doct',
                                    'defaults' => [
                                        'action' => 'ecole-doct',
                                    ],
                                ],
                            ],
                            'unite-rech' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/unite-rech',
                                    'defaults' => [
                                        'action' => 'unite-rech',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'log' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/log',
                            'defaults' => [
                                'controller' => LogController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'individu' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/individu',
                                    'defaults' => [
                                        'action' => 'individu',
                                    ],
                                ],
                            ],
                            'doctorant' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/doctorant',
                                    'defaults' => [
                                        'action' => 'doctorant',
                                    ],
                                ],
                            ],
                            'structure' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/structure',
                                    'defaults' => [
                                        'action' => 'structure',
                                    ],
                                ],
                            ],
                            'etablissement' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/etablissement',
                                    'defaults' => [
                                        'action' => 'etablissement',
                                    ],
                                ],
                            ],
                            'ecole-doct' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ecole-doct',
                                    'defaults' => [
                                        'action' => 'ecole-doct',
                                    ],
                                ],
                            ],
                            'unite-rech' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/unite-rech',
                                    'defaults' => [
                                        'action' => 'unite-rech',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
            SubstitutionController::class => SubstitutionControllerFactory::class,
            DoublonController::class => DoublonControllerFactory::class,
            ForeignKeyController::class => ForeignKeyControllerFactory::class,
            LogController::class => LogControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            SubstitutionService::class => SubstitutionServiceFactory::class,
            DoublonService::class => DoublonServiceFactory::class,
            ForeignKeyService::class => ForeignKeyServiceFactory::class,
            LogService::class => LogServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];