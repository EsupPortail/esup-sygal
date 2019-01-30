<?php

use Application\Controller\Factory\UtilisateurControllerFactory;
use Application\Form\CreationUtilisateurForm;
use Application\Form\CreationUtilisateurFromIndividuForm;
use Application\Form\Factory\CreationUtilisateurFormFactory;
use Application\Form\Factory\CreationUtilisateurFromIndividuFormFactory;
use Application\Form\Factory\CreationUtilisateurHydratorFactory;
use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Application\Form\Validator\Factory\NewEmailValidatorFactory;
use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\PasswordValidator;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Application\Service\Individu\IndividuServiceFactory;
use Application\Service\Utilisateur\UtilisateurService;
use Application\View\Helper\IndividuUsurpationHelperFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'selectionner-profil', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-identite', 'roles' => []],
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'usurper-individu', 'roles' => []],
            ],
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'index',
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
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_MODIFICATION,
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
//                    'route'    => '/[:language/]utilisateur',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Utilisateur',
                        'action'        => 'index',
//                        'language'      => 'fr_FR',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'ajouter-pour-individu' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/ajouter-pour-individu/:individu',
                            'defaults'    => [
                                'action' => 'ajouterFromIndividu',
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

                                'order'    => 60,
                            ],
                            'creation' => [
                                'label'    => "Création d'utilisateur",
                                'route'    => 'utilisateur/ajouter',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'ajouter'),

                                'order'    => 50,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => array(
            'UtilisateurService' => UtilisateurService::class,
        ),
        'factories' => [
            'IndividuService' => IndividuServiceFactory::class,
        ],
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
