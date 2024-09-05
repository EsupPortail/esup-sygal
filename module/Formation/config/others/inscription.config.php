<?php

namespace Formation;

use Application\Navigation\ApplicationNavigationFactory;
use Formation\Assertion\Inscription\InscriptionAssertion;
use Formation\Assertion\Inscription\InscriptionAssertionFactory;
use Formation\Controller\InscriptionController;
use Formation\Controller\InscriptionControllerFactory;
use Formation\Controller\Recherche\InscriptionRechercheController;
use Formation\Controller\Recherche\InscriptionRechercheControllerFactory;
use Formation\Provider\Privilege\InscriptionPrivileges;
use Formation\Service\Exporter\Attestation\AttestationExporter;
use Formation\Service\Exporter\Attestation\AttestationExporterFactory;
use Formation\Service\Exporter\Convocation\ConvocationExporter;
use Formation\Service\Exporter\Convocation\ConvocationExporterFactory;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Inscription\InscriptionServiceFactory;
use Formation\Service\Inscription\Search\InscriptionSearchService;
use Formation\Service\Inscription\Search\InscriptionSearchServiceFactory;
use Formation\View\Helper\InscriptionViewHelper;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Inscription' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            InscriptionPrivileges::INSCRIPTION_AJOUTER
                        ],
                        'resources'  => ['Inscription'],
                        'assertion'  => InscriptionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InscriptionRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_INDEX,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'generer-export-csv'
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_INDEX,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'ajouter',
                        'desinscription',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_AJOUTER,
                    ],
                    'assertion' => InscriptionAssertion::class,
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'passer-liste-principale',
                        'passer-liste-complementaire',
                        'retirer-liste',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_MODIFIER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_HISTORISER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'generer-convocation',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_CONVOCATION,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'generer-attestation',
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_ATTESTATION,
                    ],
                ],
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'accorder-sursis'
                    ],
                    'privileges' => [
                        InscriptionPrivileges::INSCRIPTION_SURSIS,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    ApplicationNavigationFactory::FORMATIONS_PAGE_ID => [
                        'pages' => [
                            'inscription' => [
                                'label'    => 'Inscriptions',
                                'route'    => 'formation/inscription',
                                'resource' => PrivilegeController::getResourceId(InscriptionRechercheController::class, 'index') ,
                                'order'    => 500,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'inscription' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/inscription',
                            'defaults' => [
                                'controller' => InscriptionRechercheController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:session[/:doctorant]',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                            'desinscription' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/desinscription/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'desinscription',
                                    ],
                                ],
                            ],
                            'passer-liste-principale' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/passer-liste-principale/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'passer-liste-principale',
                                    ],
                                ],
                            ],
                            'passer-liste-complementaire' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/passer-liste-complementaire/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'passer-liste-complementaire',
                                    ],
                                ],
                            ],
                            'retirer-liste' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/retirer-liste/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'retirer-liste',
                                    ],
                                ],
                            ],
                            'generer-convocation' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/generer-convocation/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'generer-convocation',
                                    ],
                                ],
                            ],
                            'generer-attestation' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/generer-attestation/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'generer-attestation',
                                    ],
                                ],
                            ],
                            'accorder-sursis' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/accorder-sursis/:inscription',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'accorder-sursis',
                                    ],
                                ],
                            ],
                            'generer-export-csv' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'    => '/generer-export-csv',
                                    'defaults' => [
                                        /** @see InscriptionController::genererExportCsvAction() */
                                        'controller'    => InscriptionController::class,
                                        'action'        => 'generer-export-csv',
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
            InscriptionService::class => InscriptionServiceFactory::class,
            InscriptionSearchService::class => InscriptionSearchServiceFactory::class,
            AttestationExporter::class => AttestationExporterFactory::class,
            ConvocationExporter::class => ConvocationExporterFactory::class,
            InscriptionAssertion::class => InscriptionAssertionFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            InscriptionController::class => InscriptionControllerFactory::class,
            InscriptionRechercheController::class => InscriptionRechercheControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ],

    'view_helpers' => [
        'invokables' => [
            'inscription' => InscriptionViewHelper::class,
        ],
    ],

];