<?php

namespace Soutenance;

use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
use Soutenance\Controller\EngagementImpartialite\EngagementImpartialiteController;
use Soutenance\Controller\Presoutenance\PresoutenanceController;
use Soutenance\Controller\Presoutenance\PresoutenanceControllerFactory;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormFactory;
use Soutenance\Form\DateRenduRapport\DateRenduRapportHydrator;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                            PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                            PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => PresoutenanceAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'presoutenance',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'date-rendu-rapport',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'associer-jury',
                        'deassocier-jury',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'notifier-demande-avis-soutenance',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'revoquer-avis-soutenance'
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_ANNULER,
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/soutenance[/:these]',
                    'defaults' => [
                        'controller' => SoutenanceController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'presoutenance' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/presoutenance',
                            'defaults' => [
                                'controller' => PresoutenanceController::class,
                                'action'     => 'presoutenance',
                            ],
                        ],
                        'child_routes' => [
                            'date-rendu-rapport' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/date-rendu-rapport',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'date-rendu-rapport',
                                    ],
                                ],
                            ],
                            'notifier-engagement-impartialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/notifier-engagement-impartialite',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action'     => 'notifier-rapporteurs-engagement-impartialite',
                                    ],
                                ],
                            ],
                            'associer-jury' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/associer-jury/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'associer-jury',
                                    ],
                                ],
                            ],
                            'deassocier-jury' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/deassocier-jury/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'deassocier-jury',
                                    ],
                                ],
                            ],
                            'notifier-demande-avis-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/notifier-demande-avis-soutenance[/:membre]',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'notifier-demande-avis-soutenance',
                                    ],
                                ],
                            ],
                            'revoquer-avis-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/revoquer-avis-soutenance/:avis',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'revoquer-avis-soutenance',
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
            PresoutenanceAssertion::class => PresoutenanceAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PresoutenanceController::class => PresoutenanceControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            DateRenduRapportForm::class => DateRenduRapportFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            DateRenduRapportHydrator::class => DateRenduRapportHydrator::class,
        ],
        'factories' => [
        ],
    ],
];