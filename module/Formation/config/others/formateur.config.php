<?php /** @noinspection PhpUnusedAliasInspection */

namespace Formation;

use Formation\Controller\FormateurController;
use Formation\Controller\FormateurControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use Formation\Provider\Privilege\SessionPrivileges;
use Formation\Service\Formateur\FormateurService;
use Formation\Service\Formateur\FormateurServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FormateurController::class,
                    'action' => [
                        'ajouter',
                        'retirer',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_MODIFIER,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'formateur' => [
                        'type'  => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route'    => '/formateur',
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:session',
                                    'defaults' => [
                                        'controller' => FormateurController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'retirer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/retirer/:formateur',
                                    'defaults' => [
                                        'controller' => FormateurController::class,
                                        'action'     => 'retirer',
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
            FormateurService::class => FormateurServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            FormateurController::class => FormateurControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];