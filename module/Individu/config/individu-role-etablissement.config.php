<?php

namespace Individu;

use Individu\Controller\IndividuRoleEtablissement\IndividuRoleEtablissementController;
use Individu\Controller\IndividuRoleEtablissement\IndividuRoleEtablissementControllerFactory;
use Individu\Fieldset\IndividuRoleEtablissement\IndividuRoleEtablissementFieldset;
use Individu\Fieldset\IndividuRoleEtablissement\IndividuRoleEtablissementFieldsetFactory;
use Individu\Fieldset\IndividuRoleEtablissement\IndividuRoleEtablissementHydrator;
use Individu\Fieldset\IndividuRoleEtablissement\IndividuRoleEtablissementHydratorFactory;
use Individu\Service\IndividuRoleEtablissement\IndividuRoleEtablissementService;
use Individu\Service\IndividuRoleEtablissement\IndividuRoleEtablissementServiceFactory;
use Laminas\Router\Http\Literal;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuRoleEtablissementController::class,
                    'action' => [
                        'rechercher-etablissement',
                    ],
                    'roles' => [],
//                    'privilege' => [
//                        UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
//                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu-role-etablissement' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/individu-role-etablissement',
                    'defaults' => [
                        'controller' => IndividuRoleEtablissementController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'rechercher-etablissement' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/rechercher-etablissement',
                            'defaults' => [
                                /** @see IndividuRoleEtablissementController::rechercherEtablissementAction() */
                                'action' => 'rechercher-etablissement',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuRoleEtablissementController::class => IndividuRoleEtablissementControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            IndividuRoleEtablissementService::class => IndividuRoleEtablissementServiceFactory::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            IndividuRoleEtablissementFieldset::class => IndividuRoleEtablissementFieldsetFactory::class,
        ]
    ],
    'hydrators' => [
        'factories' => [
            IndividuRoleEtablissementHydrator::class => IndividuRoleEtablissementHydratorFactory::class,
        ]
    ],
];