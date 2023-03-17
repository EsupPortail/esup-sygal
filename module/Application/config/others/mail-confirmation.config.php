<?php

use Application\Controller\Factory\MailConfirmationControllerFactory;
use Application\Controller\MailConfirmationController;
use Application\Form\Factory\MailConfirmationFormFactory;
use Application\Form\Factory\MailConfirmationHydratorFactory;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Application\Service\MailConfirmationServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => MailConfirmationController::class,
                    'action' => [
                        'index',
                        'envoie',
                        'envoye',
                        'reception',
                        'swap',
                        'remove'
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => MailConfirmationController::class,
                    'action' => [
                        'envoie',
                        'envoye',
                        'reception',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_FICHE,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'mail-confirmation' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/mail-confirmation',
                    'defaults' => [
                        'controller' => MailConfirmationController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'acceuil' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/acceuil[/:id]',
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'swap' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/swap/:id',
                            'defaults' => [
                                'action' => 'swap',
                            ],
                        ],
                    ],
                    'remove' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/remove/:id',
                            'defaults' => [
                                'action' => 'remove',
                            ],
                        ],
                    ],
                    'envoie' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/envoi/:id',
                            'defaults' => [
                                'action' => 'envoie',
                            ],
                        ],
                    ],
                    'envoye' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/envoye/:id',
                            'defaults' => [
                                'action' => 'envoye',
                            ],
                        ],
                    ],
                    'reception' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/reception/:id/:code',
                            'defaults' => [
                                'action' => 'reception',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'MailConfirmationService' => MailConfirmationServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            MailConfirmationController::class => MailConfirmationControllerFactory::class,
        ],
    ],
    'form_elements' => [
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
