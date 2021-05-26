<?php

namespace Soutenance;

use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\EngagementImpartialiteControllerFactory;
use Soutenance\Controller\RapporteurController;
use Soutenance\Controller\RapporteurControllerFactory;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Router\Http\Segment;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => RapporteurController::class,
                    'action' => [
                        'index',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'child_routes' => [
                    'rapporteur' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/rapporteur/:these/:membre',
                            'defaults' => [
                                'controller' => RapporteurController::class,
                                'action' => 'index',
                            ],
                        ],
                        'child_routes' => [
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            RapporteurController::class => RapporteurControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],

    'hydrators' => [
        'invokables' => [
        ],
    ],
);
