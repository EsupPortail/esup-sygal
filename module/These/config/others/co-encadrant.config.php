<?php

namespace These;

use These\Assertion\Acteur\ActeurAssertion;
use These\Assertion\These\TheseAssertion;
use These\Controller\CoEncadrantController;
use These\Controller\Factory\CoEncadrantControllerFactory;
use These\Form\CoEncadrant\RechercherCoEncadrantFormFactory;
use These\Form\CoEncadrant\RechercherCoEncadrantForm;
use These\Provider\Privilege\CoEncadrantPrivileges;
use These\Provider\Privilege\ThesePrivileges;
use These\Service\CoEncadrant\CoEncadrantService;
use These\Service\CoEncadrant\CoEncadrantServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            CoEncadrantPrivileges::COENCADRANT_GERER,
                        ],
                        'resources' => ['Acteur'],
                        'assertion'  => ActeurAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => CoEncadrantController::class,
                    'action'     => [
                        'index',
                        'historique',
                        'rechercher-co-encadrant',
                        'generer-justificatif-coencadrements',
                        'generer-export-csv',
                    ],
                    'privileges' => [
                        CoEncadrantPrivileges::COENCADRANT_AFFICHER,
                    ],
                ],
                [
                    'controller' => CoEncadrantController::class,
                    'action'     => [
                        'ajouter-co-encadrant',
                        'retirer-co-encadrant',
                    ],
                    'privileges' => [
                        CoEncadrantPrivileges::COENCADRANT_GERER,
                    ],
                    'assertion'  => ActeurAssertion::class,
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'co-encadrant' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/co-encadrant',
                    'defaults' => [
                        /** @see CoEncadrantController::indexAction() */
                        'controller'    => CoEncadrantController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'historique' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/historique[/:co-encadrant]',
                            'defaults' => [
                                /** @see CoEncadrantController::historiqueAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'historique',
                            ],
                        ],
                    ],
                    'generer-justificatif-coencadrements' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/generer-justificatif-coencadrements/:co-encadrant',
                            'defaults' => [
                                /** @see CoEncadrantController::genererJustificatifCoencadrementsAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'generer-justificatif-coencadrements',
                            ],
                        ],
                    ],
                    'generer-export-csv' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/generer-export-csv/:structure-type/:structure-id',
                            'defaults' => [
                                /** @see CoEncadrantController::genererExportCsvAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'generer-export-csv',
                            ],
                        ],
                    ],
                    'rechercher-co-encadrant' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/rechercher-co-encadrant',
                            'defaults' => [
                                /** @see CoEncadrantController::rechercherCoEncadrantAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'rechercher-co-encadrant',
                            ],
                        ],
                    ],
                    'ajouter-co-encadrant' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/ajouter-co-encadrant/:these',
                            'defaults' => [
                                /** @see CoEncadrantController::ajouterCoEncadrantAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'ajouter-co-encadrant',
                            ],
                        ],
                    ],
                    'retirer-co-encadrant' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/retirer-co-encadrant/:these/:co-encadrant',
                            'defaults' => [
                                /** @see CoEncadrantController::retirerCoEncadrantAction() */
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'retirer-co-encadrant',
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
                            'co-encadrant' => [
                                'label'    => 'Co-encadrant',
                                'route'    => 'co-encadrant',
                                'resource' => PrivilegeController::getResourceId(CoEncadrantController::class, 'index'),
                                'icon' => 'fas fa-user-friends',
                                'order'    => 1060,
//                                'pages' => [
//                                    'historique' => [
//                                        'label'    => 'Historique',
//                                        'route'    => 'co-encadrant/historique',
//                                        'resource' => PrivilegeController::getResourceId(CoEncadrantController::class, 'historique'),
//                                    ],
//                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            CoEncadrantController::class => CoEncadrantControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            RechercherCoEncadrantForm::class => RechercherCoEncadrantFormFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            CoEncadrantService::class => CoEncadrantServiceFactory::class,
        ],
    ],
];
