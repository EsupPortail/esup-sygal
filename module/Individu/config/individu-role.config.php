<?php

namespace Individu;

use Application\Provider\Privilege\UtilisateurPrivileges;
use Individu\Controller\IndividuRole\IndividuRoleController;
use Individu\Controller\IndividuRole\IndividuRoleControllerFactory;
use Individu\Form\IndividuRole\IndividuRoleForm;
use Individu\Form\IndividuRole\IndividuRoleFormFactory;
use Individu\Form\IndividuRole\IndividuRoleHydrator;
use Individu\Form\IndividuRole\IndividuRoleHydratorFactory;
use Individu\Service\IndividuRole\IndividuRoleService;
use Individu\Service\IndividuRole\IndividuRoleServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuRoleController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privilege' => [
                        UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu-role' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/individu-role',
                    'defaults' => [
                        'controller' => IndividuRoleController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:individu/:role',
                            'constraints'   => [
                                'individu' => '\d+',
                                'role' => '\d+',
                            ],
                            'defaults' => [
                                /** @see IndividuRoleController::modifierAction() */
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuRoleController::class => IndividuRoleControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            IndividuRoleService::class => IndividuRoleServiceFactory::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            IndividuRoleForm::class => IndividuRoleFormFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            IndividuRoleHydrator::class => IndividuRoleHydratorFactory::class,
        ]
    ],
];