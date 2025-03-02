<?php

namespace Application;

use Application\Controller\Factory\UtilisateurControllerFactory;
use Application\Form\CreationUtilisateurForm;
use Application\Form\CreationUtilisateurFromIndividuForm;
use Application\Form\Factory\CreationUtilisateurFormFactory;
use Application\Form\Factory\CreationUtilisateurFromIndividuFormFactory;
use Application\Form\Factory\CreationUtilisateurHydratorFactory;
use Application\Form\Factory\InitCompteFormFactory;
use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Application\Form\InitCompteForm;
use Application\Form\Validator\Factory\NewEmailValidatorFactory;
use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\PasswordValidator;
use Application\Process\Utilisateur\UtilisateurProcess;
use Application\Process\Utilisateur\UtilisateurProcessFactory;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Application\Service\Utilisateur\UtilisateurSearchService;
use Application\Service\Utilisateur\UtilisateurSearchServiceFactory;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Utilisateur\UtilisateurServiceFactory;
use Individu\View\Helper\IndividuUsurpationHelperFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
//                ['controller' => 'UnicaenAuthentification\Controller\Utilisateur', 'action' => 'selectionner-profil', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-identite', 'roles' => 'guest'],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'stopper-usurpation', 'roles' => 'guest'],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-individu', 'roles' => 'guest'],
            ],
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'index',
                        'voir',
                        'rechercher-people',
                        'rechercher-individu',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'retirer-role',
                        'ajouter-role',
                        'ajouter',
                        'ajouterFromIndividu',
                        'supprimer',
                        'lier-individu',
                        'lier-nouvel-individu',
                        'delier-individu',
                        'ajouter-token'
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_MODIFICATION,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'gerer-utilisateur',
                        'creer-compte-local-individu',
                        'reset-password',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_CREATE_FROM_INDIVIDU,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'register',
                        'init-compte',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'appariemment',
                    ],
                    'roles' => ['user'],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'unicaen-utilisateur' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/utilisateur',
                    'defaults' => [
                        'controller'    => 'Application\Controller\Utilisateur', // surcharge la config d'unicaen/utilisateur
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'voir' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/voir/:utilisateur',
                            'defaults'    => [
                                'controller' => 'Application\Controller\Utilisateur', // nécessaire pour surcharger la config d'unicaen/utilisateur
                                'action' => 'voir',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'controller' => 'Application\Controller\Utilisateur', // nécessaire pour surcharger la config d'unicaen/utilisateur
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer/:utilisateur',
                            'defaults'    => [
                                'controller' => 'Application\Controller\Utilisateur', // nécessaire pour surcharger la config d'unicaen/utilisateur
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                ],
            ],
            'utilisateur' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/utilisateur',
                    'defaults' => [
                        'controller'    => 'Application\Controller\Utilisateur',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'selectionner-profil' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/selectionner-profil',
                            'defaults'    => [
                                'controller'    => 'UnicaenAuthentification\Controller\Utilisateur',
                                'action' => 'selectionner-profil',
                            ],
                        ],
                    ],
                    'gerer-utilisateur' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/gerer-utilisateur/:individu',
                            'defaults'    => [
                                'action' => 'gerer-utilisateur',
                            ],
                        ],
                    ],
                    'lier-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/lier-individu/:utilisateur[/:individu]',
                            'defaults'    => [
                                'action' => 'lier-individu',
                            ],
                        ],
                    ],
                    'lier-nouvel-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/lier-nouvel-individu/:utilisateur',
                            'defaults'    => [
                                'action' => 'lier-nouvel-individu',
                            ],
                        ],
                    ],
                    'delier-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/delier-individu/:utilisateur',
                            'defaults'    => [
                                'action' => 'delier-individu',
                            ],
                        ],
                    ],
                    'creer-compte-local-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/creer-compte-local-individu/:individu',
                            'defaults'    => [
                                'action' => 'creer-compte-local-individu',
                            ],
                        ],
                    ],
                    'reset-password' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/reset-password/:individu',
                            'defaults'    => [
                                'action' => 'reset-password',
                            ],
                        ],
                    ],
                    'init-compte' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/init-compte/:token',
                            'defaults'    => [
                                /** @see \Application\Controller\UtilisateurController::initCompteAction() */
                                'action' => 'init-compte',
                            ],
                        ],
                    ],
                    'appariemment' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/appariemment/:these/:individu',
                            'defaults'    => [
                                'action' => 'appariemment',
                            ],
                        ],
                    ],
                    'rechercher-people' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/rechercher-people',
                            'defaults'    => [
                                'action' => 'rechercher-people',
                            ],
                        ],
                    ],
                    'rechercher-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
                            ],
                        ],
                    ],
                    'retirer-role' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/retirer-role/:individu/:role',
                            'defaults'    => [
                                'action' => 'retirer-role',
                            ],
                        ],
                    ],
                    'ajouter-role' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/ajouter-role/:individu/:role',
                            'defaults'    => [
                                'action' => 'ajouter-role',
                            ],
                        ],
                    ],
                    'ajouter-token' => [
                        'type'          => Segment::class,
                        'options'       => [
                            /** @see \Application\Controller\UtilisateurController::ajouterTokenAction() */
                            'route'       => '/ajouter-token/:utilisateur',
                            'defaults'    => [
                                'action' => 'ajouter-token',
                            ],
                        ],
                    ],

                ],
            ],
            'zfcuser' => [
                'child_routes' => [
                    'register' => [
                        'options' => [
                            'defaults' => [
                                'controller' => 'Application\Controller\Utilisateur',
                                'action' => 'register',
                            ],
                        ],
                    ],
                ],
            ]
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'index' => [
                                'label'    => 'Comptes utilisateurs',
                                'route'    => 'utilisateur',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'index'),
                                'icon'     => 'fa fa-users',
                                'order'    => 60,
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
            'UtilisateurService' => UtilisateurServiceFactory::class,
            UtilisateurProcess::class => UtilisateurProcessFactory::class,
            UtilisateurSearchService::class => UtilisateurSearchServiceFactory::class,
        ],
        'aliases' => [
            UtilisateurService::class => 'UtilisateurService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\Utilisateur' => UtilisateurControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            InitCompteForm::class => InitCompteFormFactory::class,
            CreationUtilisateurForm::class => CreationUtilisateurFormFactory::class,
            CreationUtilisateurFromIndividuForm::class => CreationUtilisateurFromIndividuFormFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            CreationUtilisateurHydrator::class => CreationUtilisateurHydratorFactory::class,
        ]
    ],
    'validators' => [
        'invokables' => [
            PasswordValidator::class => PasswordValidator::class,
        ],
        'factories' => [
            NewEmailValidator::class => NewEmailValidatorFactory::class,
        ],
    ],
    'view_helpers'  => [
        'factories'  => [
            'individuUsurpation' => IndividuUsurpationHelperFactory::class,
        ],
    ],
];
