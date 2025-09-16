<?php

namespace Soutenance;

use HDR\Provider\Privileges\HDRPrivileges;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
use Soutenance\Controller\HDR\Presoutenance\PresoutenanceHDRController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\These\Presoutenance\PresoutenanceTheseController;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceFormFactory;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceHydrator;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportFormFactory;
use Soutenance\Form\DateRenduRapport\DateRenduRapportHydrator;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Service\Adresse\AdresseService;
use Soutenance\Service\Adresse\AdresseServiceFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;

// génération des routes dont seul le contrôleur change (soit These, soit HDR)
$soutenanceChildRoutes = fn(string $controllerClass) => [
    // TODO :: doit devenir une route console ...
    'notifier-retard-rapport-presoutenance' => [
        'type' => Literal::class,
        'may_terminate' => true,
        'options' => [
            'route' => '/notifier-retard-rapport-presoutenance',
            'defaults' => [
                'controller' => $controllerClass,
                /** @see PresoutenanceController::notifierRetardRapportPresoutenanceAction() */
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
                'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
                        'action' => 'convocation-doctorant',
                    ],
                ],
            ],
            'convocation-candidat' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/convocation-candidat',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'convocation-candidat',
                    ],
                ],
            ],
            'convocation-membre' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/convocation-membre/:membre',
                    'defaults' => [
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
                        'action' => 'proces-verbal-soutenance',
                    ],
                ],
            ],
            'transmettre-documents-direction' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/transmettre-documents-direction',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'transmettre-documents-direction',
                    ],
                ],
            ],
            'date-rendu-rapport' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/date-rendu-rapport',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'date-rendu-rapport',
                    ],
                ],
            ],
            'notifier-rapporteurs-engagement-impartialite' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/notifier-rapporteurs-engagement-impartialite',
                    'defaults' => [
                        /** @see PresoutenanceController::notifierRapporteursEngagementImpartialiteAction */
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
                        'action' => 'deassocier-jury',
                    ],
                ],
            ],
            'associer-jury-sygal' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/associer-jury-sygal/:membre',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'associer-jury-sygal',
                    ],
                ],
            ],
            'renseigner-president-jury' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/renseigner-president-jury/:acteur',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'renseigner-president-jury',
                    ],
                ],
            ],
            'dissocier-president-jury' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/dissocier-president-jury/:acteur',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'dissocier-president-jury',
                    ],
                ],
            ],
            'notifier-demande-avis-soutenance' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/notifier-demande-avis-soutenance[/:membre]',
                    'defaults' => [
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
                        'action' => 'revoquer-avis-soutenance',
                    ],
                ],
            ],
            'indiquer-dossier-complet' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/indiquer-dossier-complet',
                    'defaults' => [
                        /** @see PresoutenanceController::indiquerDossierCompletAction() */
                        'controller' => $controllerClass,
                        'action' => 'indiquer-dossier-complet',
                    ],
                ],
            ],
            'feu-vert' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/feu-vert',
                    'defaults' => [
                        'controller' => $controllerClass,
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
                        'controller' => $controllerClass,
                        'action' => 'stopper-demarche',
                    ],
                ],
            ],
            'deliberation-jury' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/deliberation-jury/:resultat',
                    'defaults' => [
                        'controller' => $controllerClass,
                        'action' => 'deliberation-jury',
                    ],
                ],
            ],
        ],
    ],
];

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
//                        'generer-simulation',
//                        'nettoyer-simulation',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_SIMULER_REMONTEES,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
//                        'presoutenance',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                    'assertion' => PresoutenanceAssertion::class,

                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'date-rendu-rapport',
                        'indiquer-dossier-complet',
                        'feu-vert',
                        'stopper-demarche',
//                        'avis-soutenance',
//                        'convocations',
                        'envoyer-convocation',
//                        'transmettre-documents-direction',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'deliberation-jury',
                    ],
                    'privileges' => [
                        HDRPrivileges::HDR_DONNER_RESULTAT,
                        ThesePrivileges::THESE_DONNER_RESULTAT
                    ],
                    'assertion' => PresoutenanceAssertion::class,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'convocation-doctorant',
                        'convocation-membre',
//                        'proces-verbal-soutenance',
//                        'rapport-soutenance',
//                        'rapport-technique',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action' => [
                        'associer-jury',
                        'renseigner-president-jury',
                        'dissocier-president-jury',
//                        'deassocier-jury',
//                        'associer-jury-sygal'
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
            'soutenance_these' => [
                'child_routes' => $soutenanceChildRoutes(PresoutenanceTheseController::class),
            ],
            'soutenance_hdr' => [
                'child_routes' => $soutenanceChildRoutes(PresoutenanceHDRController::class),
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            PresoutenanceAssertion::class => PresoutenanceAssertionFactory::class,
            AdresseService::class => AdresseServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
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