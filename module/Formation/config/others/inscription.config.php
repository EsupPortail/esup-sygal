<?php

namespace Formation;

use Formation\Controller\InscriptionController;
use Formation\Controller\InscriptionControllerFactory;
use Formation\Provider\Privilege\InscriptionPrivileges;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Inscription\InscriptionServiceFactory;
use Formation\View\Helper\InscriptionViewHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_INDEX,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'ajouter',
                        'desinscription',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_AJOUTER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'passer-liste-principale',
                        'passer-liste-complementaire',
                        'retirer-liste',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_MODIFIER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_HISTORISER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'generer-convocation',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_CONVOCATION,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'generer-attestation',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_ATTESTATION,
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
                            'inscription' => [
                                'label'    => 'Inscriptions',
                                'route'    => 'formation/inscription',
                                'resource' => PrivilegeController::getResourceId(InscriptionController::class, 'index') ,
                                'order'    => 500,
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
                    'inscription' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/inscription',
                            'defaults' => [
                                'controller' => InscriptionController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:session[/:doctorant]',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                            'desinscription' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/desinscription/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'desinscription',
                                    ],
                                ],
                            ],
                            'passer-liste-principale' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/passer-liste-principale/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'passer-liste-principale',
                                    ],
                                ],
                            ],
                            'passer-liste-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/passer-liste-complementaire/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'passer-liste-complementaire',
                                    ],
                                ],
                            ],
                            'retirer-liste' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/retirer-liste/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'retirer-liste',
                                    ],
                                ],
                            ],
                            'generer-convocation' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/generer-convocation/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'generer-convocation',
                                    ],
                                ],
                            ],
                            'generer-attestation' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/generer-attestation/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'generer-attestation',
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
            InscriptionService::class => InscriptionServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            InscriptionController::class => InscriptionControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ],

    'view_helpers' => [
        'invokables' => [
            'inscription' => InscriptionViewHelper::class,
        ],
    ],

];