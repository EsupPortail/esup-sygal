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
use Application\Provider\Privilege\UtilisateurPrivileges;
use Application\Service\Individu\IndividuService;
use Application\Service\Individu\IndividuServiceFactory;
use Application\Service\Utilisateur\UtilisateurSearchService;
use Application\Service\Utilisateur\UtilisateurSearchServiceFactory;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Utilisateur\UtilisateurServiceFactory;
use Application\View\Helper\IndividuUsurpationHelperFactory;
use Application\View\Helper\RoleHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'selectionner-profil', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-identite', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'stopper-usurpation', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-individu', 'roles' => []],
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
                        'lier-individu',
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
            'utilisateur' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/utilisateur',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Utilisateur',
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
                                'action' => 'voir',
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
                            'route'       => '/ajouter-token/:utilisateur',
                            'defaults'    => [
                                'action' => 'ajouter-token',
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
                            'index' => [
                                'label'    => 'Utilisateurs',
                                'route'    => 'utilisateur',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'index'),
                                'icon'     => 'fa fa-users',
                                'order'    => 60,
                                'pages' => [
                                    'creation' => [
                                        'label'    => "Création",
                                        'route'    => 'utilisateur/ajouter',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'ajouter'),
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
            'IndividuService' => IndividuServiceFactory::class,
            'UtilisateurService' => UtilisateurServiceFactory::class,
            UtilisateurSearchService::class => UtilisateurSearchServiceFactory::class,
        ],
        'aliases' => [
            UtilisateurService::class => 'UtilisateurService',
            IndividuService::class => 'IndividuService',
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
        'invokables' => [
            'role' => RoleHelper::class,
        ],
        'factories'  => [
            'individuUsurpation' => IndividuUsurpationHelperFactory::class,
        ],
    ],
];
