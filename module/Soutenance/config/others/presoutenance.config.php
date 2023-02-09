<?php

namespace Soutenance;

use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\PresoutenanceControllerFactory;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceFormFactory;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceHydrator;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormFactory;
use Soutenance\Form\DateRenduRapport\DateRenduRapportHydrator;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                            PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                            PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                        ],
                        'resources' => ['These'],
                        'assertion' => PresoutenanceAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'generer-simulation',
                        'nettoyer-simulation',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_SIMULER_REMONTEES,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'presoutenance',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'date-rendu-rapport',
                        'feu-vert',
                        'stopper-demarche',
                        'avis-soutenance',
                        'convocations',
                        'modifier-adresse',
                        'envoyer-convocation',
                        'transmettre-documents-direction-these',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'convocation-doctorant',
                        'convocation-membre',
                        'proces-verbal-soutenance',
                        'rapport-soutenance',
                        'rapport-technique',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'associer-jury',
                        'deassocier-jury',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'notifier-demande-avis-soutenance',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'revoquer-avis-soutenance'
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_ANNULER,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'notifier-retard-rapport-presoutenance'
                    ],
                    'roles' => 'guest',
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'child_routes' => [
                    // TODO :: doit devenir une route console ...
                    'notifier-retard-rapport-presoutenance' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/notifier-retard-rapport-presoutenance',
                            'defaults' => [
                                'controller' => PresoutenanceController::class,
                                'action' => 'notifier-retard-rapport-presoutenance',
                            ],
                        ],
                    ],
                    'presoutenance' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/presoutenance',
                            'defaults' => [
                                'controller' => PresoutenanceController::class,
                                'action' => 'presoutenance',
                            ],
                        ],
                        'child_routes' => [
                            'generer-simulation' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/generer-simulation',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'generer-simulation',
                                    ],
                                ],
                            ],
                            'nettoyer-simulation' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/nettoyer-simulation',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'nettoyer-simulation',
                                    ],
                                ],
                            ],
                            'avis-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/avis-soutenance',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'avis-soutenance',
                                    ],
                                ],
                            ],
                            'rapport-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/rapport-soutenance',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'rapport-soutenance',
                                    ],
                                ],
                            ],
                            'rapport-technique' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/rapport-technique',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'rapport-technique',
                                    ],
                                ],
                            ],
                            'convocations' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/convocations',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'convocations',
                                    ],
                                ],
                            ],
                            'convocation-doctorant' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/convocation-doctorant',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'convocation-doctorant',
                                    ],
                                ],
                            ],
                            'convocation-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/convocation-membre/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'convocation-membre',
                                    ],
                                ],
                            ],
                            'envoyer-convocation' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/envoyer-convocation',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'envoyer-convocation',
                                    ],
                                ],
                            ],
                            'proces-verbal-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/proces-verbal-soutenance',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'proces-verbal-soutenance',
                                    ],
                                ],
                            ],
                            'transmettre-documents-direction-these' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/transmettre-documents-direction-these',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'transmettre-documents-direction-these',
                                    ],
                                ],
                            ],
                            'date-rendu-rapport' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/date-rendu-rapport',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'date-rendu-rapport',
                                    ],
                                ],
                            ],
                            'notifier-engagement-impartialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/notifier-engagement-impartialite',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action' => 'notifier-rapporteurs-engagement-impartialite',
                                    ],
                                ],
                            ],
                            'associer-jury' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/associer-jury/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'associer-jury',
                                    ],
                                ],
                            ],
                            'deassocier-jury' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/deassocier-jury/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'deassocier-jury',
                                    ],
                                ],
                            ],
                            'notifier-demande-avis-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/notifier-demande-avis-soutenance[/:membre]',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'notifier-demande-avis-soutenance',
                                    ],
                                ],
                            ],
                            'revoquer-avis-soutenance' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/revoquer-avis-soutenance/:avis',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'revoquer-avis-soutenance',
                                    ],
                                ],
                            ],
                            'feu-vert' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/feu-vert',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'feu-vert',
                                    ],
                                ],
                            ],
                            'stopper-demarche' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/stopper-demarche',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'stopper-demarche',
                                    ],
                                ],
                            ],
                            'modifier-adresse' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/modifier-adresse',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action' => 'modifier-adresse',
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
            AdresseSoutenanceForm::class => AdresseSoutenanceFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            DateRenduRapportHydrator::class => DateRenduRapportHydrator::class,
            AdresseSoutenanceHydrator::class => AdresseSoutenanceHydrator::class,
        ],
        'factories' => [
        ],
    ],
];