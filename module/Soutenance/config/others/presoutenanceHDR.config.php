<?php

namespace Soutenance;

use HDR\Provider\Privileges\HDRPrivileges;
use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Controller\HDR\Presoutenance\PresoutenanceHDRController;
use Soutenance\Controller\HDR\Presoutenance\PresoutenanceHDRControllerFactory;
use Soutenance\Controller\These\Presoutenance\PresoutenanceTheseController;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
//        'resource_providers' => [
//            'BjyAuthorize\Provider\Resource\Config' => [
//                'Acteur' => [],
//            ],
//        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                            PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                            PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                            HDRPrivileges::HDR_DONNER_RESULTAT,
                        ],
                        'resources' => ['HDR'],
                        'assertion' => PresoutenanceAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'presoutenance',
                    ],
                    'privileges' => PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                    'assertion' => PresoutenanceAssertion::class,
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
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
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'deliberation-jury',
                    ],
                    'privileges' => HDRPrivileges::HDR_DONNER_RESULTAT,
                    'assertion' => PresoutenanceAssertion::class,
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'convocation-candidat',
                        'convocation-membre',
                        'proces-verbal-soutenance',
                        'rapport-soutenance',
                        'rapport-technique',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
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
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'notifier-demande-avis-soutenance',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'notifier-rapporteurs-engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                    'assertion' => EngagementImpartialiteAssertion::class,
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
                    'action' => [
                        'revoquer-avis-soutenance'
                    ],
                    'privileges' => AvisSoutenancePrivileges::AVIS_ANNULER,
                ],
                [
                    'controller' => PresoutenanceHDRController::class,
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
            PresoutenanceHDRController::class => PresoutenanceHDRControllerFactory::class,
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