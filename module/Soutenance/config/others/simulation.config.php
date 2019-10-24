<?php

namespace Soutenance;

use Soutenance\Controller\SimulationController;
use Soutenance\Controller\SimulationControllerFactory;
use Soutenance\Form\ActeurSimule\ActeurSimuleForm;
use Soutenance\Form\ActeurSimule\ActeurSimuleFormFactory;
use Soutenance\Form\ActeurSimule\ActeurSimuleHydrator;
use Soutenance\Form\ActeurSimule\ActeurSimuleHydratorFactory;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Service\Simulation\SimulationService;
use Soutenance\Service\Simulation\SimulationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SimulationController::class,
                    'action'     => [
                        'index',
                        'ajouter-acteur-simule',
                        'modifier-acteur-simule',
                        'supprimer-acteur-simule',
                    ],
                    //TODO change that
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                ],

            ],
        ],
    ],

    'router' => [
        'routes' => [
            'simulation' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/simulation',
                    'defaults' => [
                        'controller' => SimulationController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'ajouter-acteur-simule' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/ajouter-acteur-simule',
                            'defaults' => [
                                'controller' => SimulationController::class,
                                'action'     => 'ajouter-acteur-simule',
                            ],
                        ],
                    ],
                    'modifier-acteur-simule' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/modifier-acteur-simule/:acteur',
                            'defaults' => [
                                'controller' => SimulationController::class,
                                'action'     => 'modifier-acteur-simule',
                            ],
                        ],
                    ],
                    'supprimer-acteur-simule' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/supprimer-acteur-simule/:acteur',
                            'defaults' => [
                                'controller' => SimulationController::class,
                                'action'     => 'supprimer-acteur-simule',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            SimulationService::class => SimulationServiceFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            SimulationController::class => SimulationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            ActeurSimuleForm::class => ActeurSimuleFormFactory::class,
        ],
    ],

    'hydrators' => [
        'factories' => [
            ActeurSimuleHydrator::class => ActeurSimuleHydratorFactory::class,
        ],
    ],

);
