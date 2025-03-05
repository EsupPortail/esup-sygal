<?php

namespace Soutenance;

use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Controller\These\Presoutenance\PresoutenanceTheseController;
use Soutenance\Controller\These\Presoutenance\PresoutenanceTheseControllerFactory;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

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
                            ThesePrivileges::THESE_DONNER_RESULTAT
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
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'generer-simulation',
                        'nettoyer-simulation',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_SIMULER_REMONTEES,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'presoutenance',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                    'assertion' => PresoutenanceAssertion::class,

                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'date-rendu-rapport',
                        'indiquer-dossier-complet',
                        'feu-vert',
                        'stopper-demarche',
                        'avis-soutenance',
                        'convocations',
                        'envoyer-convocation',
                        'transmettre-documents-direction',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'deliberation-jury',
                    ],
                    'privileges' => ThesePrivileges::THESE_DONNER_RESULTAT,
                    'assertion' => PresoutenanceAssertion::class,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
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
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'associer-jury',
                        'deassocier-jury',
                        'associer-jury-sygal',
                        'renseigner-president-jury',
                        'dissocier-president-jury',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'notifier-demande-avis-soutenance',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'notifier-rapporteurs-engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                    'assertion' => EngagementImpartialiteAssertion::class,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'revoquer-avis-soutenance'
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_ANNULER,
                ],
                [
                    'controller' => PresoutenanceTheseController::class,
                    'action' => [
                        'notifier-retard-rapport-presoutenance'
                    ],
                    'roles' => 'guest',
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [

        ],
    ],
    'controllers' => [
        'factories' => [
            PresoutenanceTheseController::class => PresoutenanceTheseControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],

    'hydrators' => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
];