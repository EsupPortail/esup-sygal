<?php

namespace Formation;


use Formation\Controller\EnqueteController;
use Formation\Controller\EnqueteControllerFactory;
use Formation\Form\EnqueteCategorie\EnqueteCategorieForm;
use Formation\Form\EnqueteCategorie\EnqueteCategorieFormFactory;
use Formation\Form\EnqueteCategorie\EnqueteCategorieHydrator;
use Formation\Form\EnqueteCategorie\EnqueteCategorieHydratorFactory;
use Formation\Form\EnqueteQuestion\EnqueteQuestionForm;
use Formation\Form\EnqueteQuestion\EnqueteQuestionFormFactory;
use Formation\Form\EnqueteQuestion\EnqueteQuestionHydrator;
use Formation\Form\EnqueteQuestion\EnqueteQuestionHydratorFactory;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Formation\Form\EnqueteReponse\EnqueteReponseFormFactory;
use Formation\Form\EnqueteReponse\EnqueteReponseHydrator;
use Formation\Form\EnqueteReponse\EnqueteReponseHydratorFactory;
use Formation\Provider\Privilege\EnquetePrivileges;
use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Formation\Service\EnqueteCategorie\EnqueteCategorieServiceFactory;
use Formation\Service\EnqueteQuestion\EnqueteQuestionService;
use Formation\Service\EnqueteQuestion\EnqueteQuestionServiceFactory;
use Formation\Service\EnqueteReponse\EnqueteReponseService;
use Formation\Service\EnqueteReponse\EnqueteReponseServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'afficher-questions',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_AFFICHER,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'ajouter-categorie',
                        'ajouter-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_AJOUTER,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'modifier-categorie',
                        'modifier-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_MODIFIER,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'historiser-categorie',
                        'restaurer-categorie',
                        'historiser-question',
                        'restaurer-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_HISTORISER,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'supprimer-categorie',
                        'supprimer-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'afficher-resultats',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_REPONSE_RESULTAT,
                    ],
                ],
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'repondre-questions',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_REPONSE_REPONDRE,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'formation' => [
                        'pages' => [
                            'enquete-question' => [
                                'label'    => 'Enquête - Question',
                                'route'    => 'formation/enquete/question',
                                'resource' => PrivilegeController::getResourceId(EnqueteController::class, 'afficher-questions') ,
                                'order'    => 700,
                            ],
                            'enquete-resultat' => [
                                'label'    => 'Enquête - Resultat',
                                'route'    => 'formation/enquete/resultat',
                                'resource' => PrivilegeController::getResourceId(EnqueteController::class, 'afficher-questions') ,
                                'order'    => 800,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'enquete' => [
                        'type'  => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route'    => '/enquete',
                        ],
                        'child_routes' => [
                            'resultat' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/resultat',
                                    'defaults' => [
                                        'controller' => EnqueteController::class,
                                        'action'     => 'afficher-resultats',
                                    ],
                                ],
                            ],
                            'question' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/question',
                                    'defaults' => [
                                        'controller' => EnqueteController::class,
                                        'action'     => 'afficher-questions',
                                    ],
                                ],
                                'child_routes' => [
                                    'ajouter' => [
                                        'type'  => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/ajouter',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'ajouter-question',
                                            ],
                                        ],
                                    ],
                                    'modifier' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/modifier/:question',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'modifier-question',
                                            ],
                                        ],
                                    ],
                                    'historiser' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/historiser/:question',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'historiser-question',
                                            ],
                                        ],
                                    ],
                                    'restaurer' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/restaurer/:question',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'restaurer-question',
                                            ],
                                        ],
                                    ],
                                    'supprimer' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/supprimer/:question',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'supprimer-question',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'categorie' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/categorie',
                                    'defaults' => [
                                    ],
                                ],
                                'child_routes' => [
                                    'ajouter' => [
                                        'type'  => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/ajouter',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'ajouter-categorie',
                                            ],
                                        ],
                                    ],
                                    'modifier' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/modifier/:categorie',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'modifier-categorie',
                                            ],
                                        ],
                                    ],
                                    'historiser' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/historiser/:categorie',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'historiser-categorie',
                                            ],
                                        ],
                                    ],
                                    'restaurer' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/restaurer/:categorie',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'restaurer-categorie',
                                            ],
                                        ],
                                    ],
                                    'supprimer' => [
                                        'type'  => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/supprimer/:categorie',
                                            'defaults' => [
                                                'controller' => EnqueteController::class,
                                                'action'     => 'supprimer-categorie',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'repondre-questions' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/repondre-questions/:inscription',
                                    'defaults' => [
                                        'controller' => EnqueteController::class,
                                        'action'     => 'repondre-questions',
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
            EnqueteCategorieService::class => EnqueteCategorieServiceFactory::class,
            EnqueteQuestionService::class => EnqueteQuestionServiceFactory::class,
            EnqueteReponseService::class => EnqueteReponseServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            EnqueteController::class => EnqueteControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            EnqueteCategorieForm::class =>EnqueteCategorieFormFactory::class,
            EnqueteQuestionForm::class => EnqueteQuestionFormFactory::class,
            EnqueteReponseForm::class => EnqueteReponseFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            EnqueteCategorieHydrator::class => EnqueteCategorieHydratorFactory::class,
            EnqueteQuestionHydrator::class => EnqueteQuestionHydratorFactory::class,
            EnqueteReponseHydrator::class => EnqueteReponseHydratorFactory::class,
        ],
    ]

];