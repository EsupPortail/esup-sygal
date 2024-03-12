<?php

namespace Admission;

use Admission\Assertion\AdmissionAssertion;
use Admission\Controller\Document\DocumentController;
use Admission\Controller\Document\DocumentControllerFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => DocumentController::class,
                    'action' => [
                        'telecharger-document',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_TELECHARGER_TOUT_DOCUMENT,
                        AdmissionPrivileges::ADMISSION_TELECHARGER_SON_DOCUMENT,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => DocumentController::class,
                    'action' => [
                        'enregistrer-document',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_TELEVERSER_TOUT_DOCUMENT,
                        AdmissionPrivileges::ADMISSION_TELEVERSER_SON_DOCUMENT,
                        AdmissionPrivileges::ADMISSION_GERER_RECAPITULATIF_DOSSIER
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
                [
                    'controller' => DocumentController::class,
                    'action' => [
                        'supprimer-document',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_TOUT_DOCUMENT,
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_SON_DOCUMENT,
                        AdmissionPrivileges::ADMISSION_GERER_RECAPITULATIF_DOSSIER
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    /**
                     * @see DocumentController::enregistrerDocumentAction()
                     */
                    'enregistrer-document' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/enregistrer-document/:individu/:codeNatureFichier',
                            'constraints' => [
                                'individu' => '[0-9]*',
                            ],
                            'defaults'    => [
                                'controller' => DocumentController::class,
                                'action' => 'enregistrer-document',
                            ],
                        ],
                    ],
                    /**
                     * @see DocumentController::supprimerDocumentAction()
                     */
                    'supprimer-document' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer-document/:individu/:codeNatureFichier',
                            'constraints' => [
                                'individu' => '[0-9]*',
                                'codeNatureFichier' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults'    => [
                                'controller' => DocumentController::class,
                                'action' => 'supprimer-document',
                            ],
                        ],
                    ],
                    /**
                     * @see DocumentController::telechargerDocumentAction()
                     */
                    'telecharger-document' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/telecharger-document/:individu/:codeNatureFichier',
                            'constraints' => [
                                'individu' => '[0-9]*',
                                'codeNatureFichier' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults'    => [
                                'controller' => DocumentController::class,
                                'action' => 'telecharger-document',
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            DocumentController::class => DocumentControllerFactory::class,
        ],
    ],
);
