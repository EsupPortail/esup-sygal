<?php

use Application\Controller\AdminController;
use Application\Controller\Factory\RoleControllerFactory;
use Application\Controller\MailConfirmationController;
use Application\Provider\Privilege\UtilisateurPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Privilege\Privileges;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use Application\Form\Factory\MailConfirmationFormFactory;
use Application\Form\Factory\MailConfirmationHydratorFactory;
use Application\Service\MailConfirmationServiceFactory;
use Application\Controller\Factory\MailConfirmationControllerFactory;
use Application\Provider\Privilege\ThesePrivileges;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Admin',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => 'Application\Controller\Role',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Role',
                    'action'     => [
                        'modifier',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => 'UnicaenApp\Controller\Application',
                    'action'     => [
                        'test-envoi-mail',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => MailConfirmationController::class,
                    'action'     => [
                        'index',
                        'envoie',
                        'reception',
                        'swap',
                        'remove'
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => MailConfirmationController::class,
                    'action'     => [
                        'envoie',
                        'reception',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_FICHE,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'admin' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]admin',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Admin',
                        'action'        => 'index',
                    ],
                ],
            ],
            'roles' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/roles',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Role',
                        'action'        => 'index',
                    ],
                ],
            ],
            'mail-confirmation-acceuil' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/mail-confirmation-acceuil[/:id]',
                    'defaults' =>[
                        'controller' => MailConfirmationController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'mail-confirmation-swap' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/mail-confirmation-swap/:id',
                    'defaults' =>[
                        'controller' => MailConfirmationController::class,
                        'action' => 'swap',
                    ],
                ],
            ],
            'mail-confirmation-remove' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/mail-confirmation-remove/:id',
                    'defaults' =>[
                        'controller' => MailConfirmationController::class,
                        'action' => 'remove',
                    ],
                ],
            ],

            'mail-confirmation-envoie' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/mail-confirmation-envoie/:id',
                    'defaults' =>[
                        'controller' => MailConfirmationController::class,
                        'action' => 'envoie',
                    ],
                ],
            ],
            'mail-confirmation-reception' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/mail-confirmation-reception/:id/:code',
                    'defaults' =>[
                        'controller' => MailConfirmationController::class,
                        'action' => 'reception',
                    ],
                ],
            ],
            'modifier-privilege' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/modifier-privilege/:role/:privilege',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Role',
                        'action'        => 'modifier',
                    ],
                ],
            ],
            'test-envoi-mail' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]test-envoi-mail',
//                    'defaults' => [
//                        '__NAMESPACE__' => 'Application\Controller',
//                        'controller'    => 'Admin',
//                        'action'        => 'index',
//                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'order'    => -90,
                        'label'    => 'Administration',
                        'route'    => 'admin',
                        'icon'     => 'glyphicon glyphicon-cog',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\Admin', 'index'),
                        'pages' => [
                            'test-envoi-mail' => [
                                'label'    => 'Test envoi mail',
                                'route'    => 'test-envoi-mail',
                                //'icon'     => 'glyphicon glyphicon-send',
                                'resource' => PrivilegeController::getResourceId('UnicaenApp\Controller\Application', 'test-envoi-mail'),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'language',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'invokables' => [],
        'factories' => [
            'MailConfirmationService' => MailConfirmationServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Admin' => AdminController::class,

        ],
        'factories' => [
            'Application\Controller\Role' => RoleControllerFactory::class,
            MailConfirmationController::class => MailConfirmationControllerFactory::class,
            CobayeController::class => CobayeControllerFactory::class,

        ],
    ],

    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'MailConfirmationForm' => MailConfirmationFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            'MailConfirmationHydrator' => MailConfirmationHydratorFactory::class,
        ]
    ],

];
