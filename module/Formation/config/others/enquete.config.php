<?php

namespace Formation;

use Application\Navigation\ApplicationNavigationFactory;
use Formation\Controller\EnqueteQuestionController;
use Formation\Controller\EnqueteQuestionControllerFactory;
use Formation\Controller\EnqueteReponseController;
use Formation\Controller\EnqueteReponseControllerFactory;
use Formation\Controller\Recherche\EnqueteReponseRechercheController;
use Formation\Controller\Recherche\EnqueteReponseRechercheControllerFactory;
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
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchService;
use Formation\Service\EnqueteReponse\Search\EnqueteReponseSearchServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EnqueteQuestionController::class,
                    'action' => [
                        'afficher-questions',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_AFFICHER,
                    ],
                ],
                [
                    'controller' => EnqueteQuestionController::class,
                    'action' => [
                        'ajouter-categorie',
                        'ajouter-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_AJOUTER,
                    ],
                ],
                [
                    'controller' => EnqueteQuestionController::class,
                    'action' => [
                        'modifier-categorie',
                        'modifier-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_MODIFIER,
                    ],
                ],
                [
                    'controller' => EnqueteQuestionController::class,
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
                    'controller' => EnqueteQuestionController::class,
                    'action' => [
                        'supprimer-categorie',
                        'supprimer-question',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_QUESTION_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => EnqueteQuestionController::class,
                    'action' => [
                        'repondre-questions',
                        'valider-questions',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_REPONSE_REPONDRE,
                    ],
                ],
                [
                    'controller' => EnqueteReponseRechercheController::class,
                    'action' => [
                        'afficher-resultats',
                        'filters',
                    ],
                    'privileges' => [
                        EnquetePrivileges::ENQUETE_REPONSE_RESULTAT,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    ApplicationNavigationFactory::FORMATIONS_PAGE_ID => [
                        'pages' => [
                            'enquete-question' => [
                                'label'    => 'Enquête - Question',
                                'route'    => 'formation/enquete/question',
                                'resource' => PrivilegeController::getResourceId(EnqueteQuestionController::class, 'afficher-questions') ,
                                'order'    => 700,
                            ],
                            'enquete-resultat' => [
                                'label'    => 'Enquête - Resultat',
                                'route'    => 'formation/enquete/resultat',
                                'params'   => [
                                    'session' => EnqueteReponseRechercheController::SESSION_ROUTE_PARAM_TOUTES,
                                ],
                                'resource' => PrivilegeController::getResourceId(EnqueteQuestionController::class, 'afficher-questions') ,
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
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/resultat/session/:session',
                                    'constraints' => [
                                        'session' => '(\d+)|(' . EnqueteReponseRechercheController::SESSION_ROUTE_PARAM_TOUTES . ')',
                                    ],
                                    'defaults' => [
                                        'controller' => EnqueteReponseRechercheController::class,
                                        'action'     => 'afficher-resultats',
                                        'session'    => EnqueteReponseRechercheController::SESSION_ROUTE_PARAM_TOUTES,
                                    ],
                                ],
                                'child_routes' => [
                                    'filters' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/filters',
                                            'defaults' => [
                                                'action' => 'filters',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'question' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/question',
                                    'defaults' => [
                                        'controller' => EnqueteQuestionController::class,
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
                                        'controller' => EnqueteQuestionController::class,
                                    ],
                                ],
                                'child_routes' => [
                                    'ajouter' => [
                                        'type'  => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/ajouter',
                                            'defaults' => [
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
                                        'controller' => EnqueteQuestionController::class,
                                        'action'     => 'repondre-questions',
                                    ],
                                ],
                            ],
                            'valider-questions' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider-questions/:inscription',
                                    'defaults' => [
                                        'controller' => EnqueteQuestionController::class,
                                        'action'     => 'valider-questions',
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

            EnqueteReponseSearchService::class => EnqueteReponseSearchServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            EnqueteQuestionController::class => EnqueteQuestionControllerFactory::class,
            EnqueteReponseController::class => EnqueteReponseControllerFactory::class,

            EnqueteReponseRechercheController::class => EnqueteReponseRechercheControllerFactory::class,
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