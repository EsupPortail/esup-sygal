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
use Substitution\Provider\Privilege\SubstitutionPrivileges;
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
                    'privilege' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => SubstitutionController::class,
                    'action' => [
                        'accueil', 'liste', 'voir', 'voir-substitue', 'voir-substituant',
                        'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'privilege' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => DoublonController::class,
                    'action' => [
                        'accueil', 'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'privilege' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => ForeignKeyController::class,
                    'action' => [
                        'accueil', 'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'privilege' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
                ],
                [
                    'controller' => LogController::class,
                    'action' => [
                        'accueil', 'individu', 'doctorant', 'structure', 'etablissement', 'ecole-doct', 'unite-rech',
                    ],
                    'privilege' => [
                        SubstitutionPrivileges::SUBSTITUTION_CONSULTER,
                    ],
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
                                'action' => 'accueil',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'voir' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/[:type]/voir/[:id]',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                        'id' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'voir',
                                    ],
                                ],
                            ],
                            'liste' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/liste/[:type]',
                                    'constraints' => [
                                        'type' => Constants::TYPES_REGEXP_CONSTRAINT,
                                    ],
                                    'defaults' => [
                                        'action' => 'liste',
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
                                'action' => 'accueil',
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
                                'action' => 'accueil',
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
                    'log' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/log',
                            'defaults' => [
                                'controller' => LogController::class,
                                'action' => 'accueil',
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
                            'substitution' => [
                                'label' => 'Module Substitutions',
                                'route' => 'substitution',
                                'icon' => 'fas fa-object-group',
                                'resource' => PrivilegeController::getResourceId(SubstitutionController::class, 'accueil'),
                                'order' => 50,
                                'pages' => [
                                    'substitution' => [
                                        'label' => 'Substitutions existantes',
                                        'route' => 'substitution/substitution',
                                        'pages' => [
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_structure]
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_etablissement]
                                            ],
                                            'ecole-doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_ecole_doct]
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_unite_rech]
                                            ],
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_individu]
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/substitution/liste',
                                                'params' => ['type' => Constants::TYPE_doctorant]
                                            ],
                                        ],
                                    ],
                                    'doublon' => [
                                        'label' => 'Substitutions possibles',
                                        'route' => 'substitution/doublon',
                                        'pages' => [
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/doublon/individu',
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/doublon/doctorant',
                                            ],
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/doublon/structure',
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/doublon/etablissement',
                                            ],
                                            'ecole-doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/doublon/ecole-doct',
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/doublon/unite-rech',
                                            ],
                                        ],
                                    ],
                                    'foreign-key' => [
                                        'label' => 'Clés étrangères',
                                        'route' => 'substitution/foreign-key',
                                        'pages' => [
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/foreign-key/individu',
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/foreign-key/doctorant',
                                            ],
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/foreign-key/structure',
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/foreign-key/etablissement',
                                            ],
                                            'ecole-doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/foreign-key/ecole-doct',
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/foreign-key/unite-rech',
                                            ],
                                        ],
                                    ],
                                    'log' => [
                                        'label' => 'Logs',
                                        'route' => 'substitution/log',
                                        'pages' => [
                                            'individu' => [
                                                'label' => 'Individus',
                                                'route' => 'substitution/log/individu',
                                            ],
                                            'doctorant' => [
                                                'label' => 'Doctorants',
                                                'route' => 'substitution/log/doctorant',
                                            ],
                                            'structure' => [
                                                'label' => 'Structures',
                                                'route' => 'substitution/log/structure',
                                            ],
                                            'etablissement' => [
                                                'label' => 'Etablissements',
                                                'route' => 'substitution/log/etablissement',
                                            ],
                                            'ecole-doct' => [
                                                'label' => 'Ecoles doctorales',
                                                'route' => 'substitution/log/ecole-doct',
                                            ],
                                            'unite-rech' => [
                                                'label' => 'Unités de recherche',
                                                'route' => 'substitution/log/unite-rech',
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