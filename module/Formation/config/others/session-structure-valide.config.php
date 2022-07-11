<?php

namespace Formation;

use Formation\Controller\SessionStructureComplementaireController;
use Formation\Controller\SessionStructureComplementaireControllerFactory;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireForm;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireFormFactory;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireHydrator;
use Formation\Form\SessionStructureComplementaire\SessionStructureComplementaireHydratorFactory;
use Formation\Provider\Privilege\SessionPrivileges;
use Formation\Service\SessionStructureComplementaire\SessionStructureComplementaireService;
use Formation\Service\SessionStructureComplementaire\SessionStructureComplementaireServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SessionStructureComplementaireController::class,
                    'action' => [
                        'ajouter-structure-complementaire',
                        'modifier-structure-complementaire',
                        'historiser-structure-complementaire',
                        'restaurer-structure-complementaire',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_MODIFIER,
                    ],
                ],
                [
                    'controller' => SessionStructureComplementaireController::class,
                    'action' => [
                        'supprimer-structure-complementaire',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_SUPPRIMER,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'session' => [
                        'child_routes' => [
                            'ajouter-structure-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter-structure-complementaire/:session',
                                    'defaults' => [
                                        'controller' => SessionStructureComplementaireController::class,
                                        'action'     => 'ajouter-structure-complementaire',
                                    ],
                                ],
                            ],
                            'modifier-structure-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-structure-complementaire/:structure-complementaire',
                                    'defaults' => [
                                        'controller' => SessionStructureComplementaireController::class,
                                        'action'     => 'modifier-structure-complementaire',
                                    ],
                                ],
                            ],
                            'historiser-structure-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser-structure-complementaire/:structure-complementaire',
                                    'defaults' => [
                                        'controller' => SessionStructureComplementaireController::class,
                                        'action'     => 'historiser-structure-complementaire',
                                    ],
                                ],
                            ],
                            'restaurer-structure-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer-structure-complementaire/:structure-complementaire',
                                    'defaults' => [
                                        'controller' => SessionStructureComplementaireController::class,
                                        'action'     => 'restaurer-structure-complementaire',
                                    ],
                                ],
                            ],
                            'supprimer-structure-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer-structure-complementaire/:structure-complementaire',
                                    'defaults' => [
                                        'controller' => SessionStructureComplementaireController::class,
                                        'action'     => 'supprimer-structure-complementaire',
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
            SessionStructureComplementaireService::class => SessionStructureComplementaireServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            SessionStructureComplementaireController::class => SessionStructureComplementaireControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            SessionStructureComplementaireForm::class => SessionStructureComplementaireFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            SessionStructureComplementaireHydrator::class => SessionStructureComplementaireHydratorFactory::class,
        ],
    ]

];