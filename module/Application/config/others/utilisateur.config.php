<?php

use Application\Controller\Factory\UtilisateurControllerFactory;
use Application\Form\CreationUtilisateurForm;
use Application\Form\Factory\CreationUtilisateurFormFactory;
use Application\Form\Factory\CreationUtilisateurHydratorFactory;
use Application\Form\Hydrator\CreationUtilisateurHydrator;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Application\Service\Individu\IndividuServiceFactory;
use Application\Service\Utilisateur\UtilisateurService;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Application\Form\Validator\PasswordValidator;
use Application\Form\Validator\NewEmailValidator;
use Application\Form\Validator\Factory\NewEmailValidatorFactory;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => 'Application\Controller\Utilisateur', 'action' => 'selectionner-profil', 'roles' => []],
            ],
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'index',
                        'index-bis',
                        'retirer-role',
                        'ajouter-role',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_MODIFICATION,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'rechercher-people',
                        'rechercher-individu',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'attribuer-role',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => 'Application\Controller\Utilisateur',
                    'action'     => [
                        'creation-utilisateur',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_MODIFICATION,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'creation-utilisateur' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'       => '/creation-utilisateur',
                    'defaults'    => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Utilisateur',
                        'action' => 'creation-utilisateur',
                    ],
                ],
                'may_terminate' => true,
            ],
            'utilisateur' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]utilisateur',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Utilisateur',
                        'action'        => 'index',
                        'language'      => 'fr_FR',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'ajouter' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'attribuer-role' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/attribuer-role/:utilisateur',
                            'constraints' => [
                                'utilisateur' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'attribuer-role',
                            ],
                        ],
                    ],
                    'rechercher-people' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/rechercher-people',
                            'defaults'    => [
                                'action' => 'rechercher-people',
                            ],
                        ],
                    ],
                    'rechercher-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/rechercher-individu',
                            'defaults'    => [
                                'action' => 'rechercher-individu',
                            ],
                        ],
                    ],
                    'individu-index' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/index[/:id]',
                            'defaults'    => [
                                'action' => 'index-bis',
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
                            'utilisateur' => [
                                'label'    => 'Utilisateurs',
                                'route'    => 'utilisateur',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'index'),
                            ],
                            'creation' => [
                                'label'    => 'Création d\'utilisateur',
                                'route'    => 'creation-utilisateur',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Utilisateur', 'creation-utilisateur'),
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
            CreationUtilisateurForm::class => CreationUtilisateurFormFactory::class
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
];
