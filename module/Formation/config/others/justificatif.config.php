<?php

namespace Formation;

use Formation\Assertion\Justificatif\JustificatifAssertion;
use Formation\Assertion\Justificatif\JustificatifAssertionFactory;
use Formation\Controller\JustificatifController;
use Formation\Controller\JustificatifControllerFactory;
use Formation\Provider\Privilege\JustificatifPrivileges;
use Formation\Service\Justificatif\JustificatifService;
use Formation\Service\Justificatif\JustificatifServiceFactory;
use Formation\Service\JustificatifSeance\JustificatifSeanceService;
use Formation\Service\JustificatifSeance\JustificatifSeanceServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Justificatif' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            JustificatifPrivileges::INSCRIPTION_GERER_JUSTIFICATIF_ABSENCE,
                            JustificatifPrivileges::INSCRIPTION_ACCEDER_JUSTIFICATIF_ABSENCE,
                        ],
                        'resources'  => ['Justificatif'],
                        'assertion'  => JustificatifAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => JustificatifController::class,
                    'action' => [
                        'justifier-absence',
                        'telecharger-justificatif-absence-seance'
                    ],
                    'privileges' => [
                        JustificatifPrivileges::INSCRIPTION_ACCEDER_JUSTIFICATIF_ABSENCE,
                    ],
                    'assertion' => JustificatifAssertion::class,
                ],
                [
                    'controller' => JustificatifController::class,
                    'action' => [
                        'ajouter-justificatif-absence-seance',
                        'supprimer-justificatif-absence-seance',
                    ],
                    'privileges' => [
                        JustificatifPrivileges::INSCRIPTION_GERER_JUSTIFICATIF_ABSENCE,
                    ],
                    'assertion' => JustificatifAssertion::class,
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
                            'justifier-absence' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/justifier-absence/:inscription',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        /** @see JustificatifController::justifierAbsenceAction() */
                                        'action'     => 'justifier-absence',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'seance' => [
                        'child_routes' => [
                            'ajouter-justificatif-absence-seance' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter-justificatif-absence-seance/:seance/:inscription',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        /** @see JustificatifController::ajouterJustificatifAbsenceSeanceAction() */
                                        'action'     => 'ajouter-justificatif-absence-seance',
                                    ],
                                ],
                            ],
                            'telecharger-justificatif-absence-seance' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/telecharger-justificatif-absence-seance/:seance/:justificatif',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        /** @see JustificatifController::telechargerJustificatifAbsenceSeanceAction() */
                                        'action'     => 'telecharger-justificatif-absence-seance',
                                    ],
                                ],
                            ],
                            'supprimer-justificatif-absence-seance' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer-justificatif-absence-seance/:seance/:justificatif',
                                    'defaults' => [
                                        'controller' => JustificatifController::class,
                                        /** @see JustificatifController::supprimerJustificatifAbsenceSeanceAction() */
                                        'action'     => 'supprimer-justificatif-absence-seance',
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
            JustificatifService::class => JustificatifServiceFactory::class,
            JustificatifSeanceService::class => JustificatifSeanceServiceFactory::class,
            JustificatifAssertion::class => JustificatifAssertionFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            JustificatifController::class => JustificatifControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ]
];