<?php

namespace Depot;

/**
 * Config concernant les fichiers liés à une thèse.
 */

use Depot\Assertion\These\TheseAssertion;
use Depot\Controller\Factory\FichierHDRControllerFactory;
use Depot\Controller\FichierHDRController;
use Depot\Controller\Plugin\UrlFichierHDR;
use Depot\Provider\Privilege\DepotPrivileges;
use Depot\Provider\Privilege\ValidationPrivileges;
use Depot\Service\FichierHDR\FichierHDRService;
use Depot\Service\FichierHDR\FichierHDRServiceFactory;
use HDR\Assertion\HDRAssertion;
use Soutenance\Provider\Privilege\JustificatifPrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FichierHDRController::class,
                    'action' => [
                        'telecharger-fichier',
                    ],
                    'privileges' => DepotPrivileges::HDR_TELECHARGEMENT_FICHIER,
                    'assertion' => HDRAssertion::class,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'fichier' => [
                'child_routes' => [
                    'hdr' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/hdr/:hdr',
                            'constraints' => [
                                'hdr' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => FichierHDRController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'telecharger' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/telecharger/:fichier[/:fichierNom]',
                                    'constraints' => [
                                        'fichier' => '[a-zA-Z0-9-]{36}',
                                    ],
                                    'defaults' => [
                                        'action' => 'telecharger-fichier',
                                        /* @see FichierHDRController::telechargerFichierAction() */
                                    ],
                                ],
                            ],
                        ],
                    ], // 'hdr'
                ],
            ], // 'fichier'
        ],
    ],

    'service_manager' => [
        'factories' => [
            FichierHDRService::class => FichierHDRServiceFactory::class,
        ],
        'aliases' => [
            'FichierHDRService' => FichierHDRService::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            FichierHDRController::class => FichierHDRControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlFichierHDR' => UrlFichierHDR::class,
        ],
    ],
];
