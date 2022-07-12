<?php

namespace Formation;

use Formation\Controller\SessionStructureValideController;
use Formation\Controller\SessionStructureValideControllerFactory;
use Formation\Form\SessionStructureValide\SessionStructureValideForm;
use Formation\Form\SessionStructureValide\SessionStructureValideFormFactory;
use Formation\Form\SessionStructureValide\SessionStructureValideHydrator;
use Formation\Form\SessionStructureValide\SessionStructureValideHydratorFactory;
use Formation\Provider\Privilege\SessionPrivileges;
use Formation\Service\SessionStructureValide\SessionStructureValideService;
use Formation\Service\SessionStructureValide\SessionStructureValideServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SessionStructureValideController::class,
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
                    'controller' => SessionStructureValideController::class,
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
                                        'controller' => SessionStructureValideController::class,
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
                                        'controller' => SessionStructureValideController::class,
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
                                        'controller' => SessionStructureValideController::class,
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
                                        'controller' => SessionStructureValideController::class,
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
                                        'controller' => SessionStructureValideController::class,
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
            SessionStructureValideService::class => SessionStructureValideServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            SessionStructureValideController::class => SessionStructureValideControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            SessionStructureValideForm::class => SessionStructureValideFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            SessionStructureValideHydrator::class => SessionStructureValideHydratorFactory::class,
        ],
    ]

];