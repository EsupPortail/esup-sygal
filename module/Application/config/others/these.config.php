<?php

namespace Application;

use Application\Controller\Factory\TheseConsoleControllerFactory;
use Application\Controller\Factory\TheseControllerFactory;
use Application\Controller\Factory\TheseObserverControllerFactory;
use Application\Controller\Factory\TheseRechercheControllerFactory;
use Application\Controller\Plugin\Url\UrlThesePluginFactory;
use Application\Controller\Rapport\RapportActiviteController;
use Application\Controller\Rapport\RapportCsiController;
use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Controller\TheseConsoleController;
use Application\Controller\TheseController;
use Application\Controller\TheseRechercheController;
use Application\Entity\Db\Diffusion;
use Application\Entity\Db\WfEtape;
use Application\Form\Factory\AttestationHydratorFactory;
use Application\Form\Factory\AttestationTheseFormFactory;
use Application\Form\Factory\DiffusionHydratorFactory;
use Application\Form\Factory\DiffusionTheseFormFactory;
use Application\Form\Factory\MetadonneeTheseFormFactory;
use Application\Form\Factory\PointsDeVigilanceFormFactory;
use Application\Form\Factory\PointsDeVigilanceHydratorFactory;
use Application\Form\Factory\RdvBuHydratorFactory;
use Application\Form\Factory\RdvBuTheseDoctorantFormFactory;
use Application\Form\Factory\RdvBuTheseFormFactory;
use Application\Navigation\ApplicationNavigationFactory;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Service\Acteur\ActeurService;
use Application\Service\Acteur\ActeurServiceFactory;
use Application\Service\Financement\FinancementService;
use Application\Service\Financement\FinancementServiceFactory;
use Application\Service\Message\DiffusionMessages;
use Application\Service\PageDeCouverture\PageDeCouverturePdfExporter;
use Application\Service\PageDeCouverture\PageDeCouverturePdfExporterFactory;
use Application\Service\ServiceAwareInitializer;
use Application\Service\These\Factory\TheseObserverServiceFactory;
use Application\Service\These\Factory\TheseSearchServiceFactory;
use Application\Service\These\Factory\TheseServiceFactory;
use Application\Service\These\TheseSearchService;
use Application\Service\These\TheseService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivServiceFactory;
use Application\View\Helper\Url\UrlTheseHelperFactory;
use Soutenance\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'These' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    //
                    // Privilèges concernant la ressource These *NON* SOUMIS À ASSERTION.
                    //
                    [
                        'privileges' => [
                            ThesePrivileges::THESE_REFRESH,
                        ],
                        'resources' => ['These'],
                    ],
                    [
                        //
                        // Privilèges concernant la ressource These SOUMIS À ASSERTION.
                        //
                        'privileges' => [
                            ThesePrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE,
                            ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE,
                            ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE,
                            ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE,
                            ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE,
                            ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE,
                            ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE,
                            ThesePrivileges::THESE_DEPOT_VERSION_INITIALE,
                            ThesePrivileges::THESE_DEPOT_VERSION_CORRIGEE,
                            ThesePrivileges::THESE_TELECHARGEMENT_FICHIER,
                            ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE,
                            ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE,
                            ThesePrivileges::THESE_SAISIE_RDV_BU,
                            ThesePrivileges::THESE_FICHIER_DIVERS_TELEVERSER,
                            ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                            ThesePrivileges::THESE_CONSULTATION_TOUTES_THESES,
                            ThesePrivileges::THESE_CONSULTATION_SES_THESES,
                            ThesePrivileges::THESE_MODIFICATION_TOUTES_THESES,
                            ThesePrivileges::THESE_MODIFICATION_SES_THESES,
                        ],
                        'resources' => ['These'],
                        'assertion' => 'Assertion\\These',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TheseRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                        'notres',
                        'notresFilters',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'index',
                        'these',
                        'detail-identite',
                        'depot-accueil'
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'detail-depot-divers',
                        /* @see TheseController::detailDepotDiversAction() */
                    ],
                    'privileges' => ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'depot-papier-final',
                    ],
                    'privileges' => ThesePrivileges::THESE_RECHERCHE,
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'filters',
                    ],
                    'roles' => 'guest',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'roadmap',
                        'generate',
                        'fusion',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_CONSULTATION_FICHE,
                        ThesePrivileges::THESE_CONSULTATION_TOUTES_THESES,
                        ThesePrivileges::THESE_CONSULTATION_SES_THESES,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'refresh-these',
                    ],
                    'privileges' => ThesePrivileges::THESE_REFRESH,
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'detail-depot',
                        'detail-depot-version-corrigee',
                        'detail-fichiers',

                        'these-retraitee',
                        'annexes',
                        'attestation',
                        'diffusion',
                        'archivabilite-these',
                        'conformite-these-retraitee',

                        'exporter-convention-mise-en-ligne',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_DEPOT,
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'depot-pv-soutenance',
                        'depot-rapport-soutenance',
                        'depot-pre-rapport-soutenance',
                        'depot-demande-confident',
                        'depot-prolong-confident',
                        'depot-conv-mise-en-ligne',
                        'depot-avenant-conv-mise-en-ligne',
                    ],
                    'privileges' => ThesePrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'validation-page-de-couverture',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_CONSULTATION_PAGE_COUVERTURE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'detail-description',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_DESCRIPTION,
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'detail-archivage',
                        'detail-archivage-version-corrigee',
                        'test-archivabilite',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_ARCHIVAGE,
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'detail-rdv-bu',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_RDV_BU,
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-correction-autorisee-forcee',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-description',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE,
                        ThesePrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-attestation',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE,
                        ThesePrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-diffusion',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE,
                        ThesePrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-certif-conformite',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE,
                        ThesePrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'modifier-rdv-bu',
                        'points-de-vigilance',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_SAISIE_RDV_BU,
                    ],
                    'assertion' => 'Assertion\\These',
                ],

                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'validation-these-corrigee',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => 'Application\Controller\TheseObserver',
                    'action' => [
                        'notify-date-butoir-correction-depassee',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\These',
                    'action' => [
                        'depot-papier-final',
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_CONSULTATION_VERSION_PAPIER_CORRIGEE,
                    ],
                    'assertion' => 'Assertion\\These',
                ],
                [
                    'controller' => TheseConsoleController::class,
                    'action' => [
                        'transfer-these-data',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'depot' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/depot/:these',
                    'constraints' => [
                        'these' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'These',
                        'action' => 'depot-accueil',
                    ],
                ],
                'may_terminate' => true,
            ],
            'these' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/these',
                    'defaults' => [
                        'controller' => TheseRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'recherche' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => TheseRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
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
                            'notres' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/notres',
                                    'defaults' => [
                                        'action' => 'notres',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filters' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/filters',
                                            'defaults' => [
                                                'action' => 'notresFilters',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'roadmap' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/roadmap/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'roadmap',
                            ],
                        ],
                    ],
                    'identite' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/identite/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-identite',
                            ],
                        ],
                    ],
                    'generate' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/generate/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'generate',
                            ],
                        ],
                    ],
                    'fusion' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/fusion/:these[/:corrigee[/:version[/:removal]]]',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'fusion',
                            ],
                        ],
                    ],
                    'description' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/description/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-description',
                            ],
                        ],
                    ],
                    'refresh-these' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/refresh/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'refresh-these',
                            ],
                        ],
                    ],
                    'version-papier' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/depot-papier-final/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'depot-papier-final',
                            ],
                        ],
                    ],
                    'validation-page-de-couverture' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/validation-page-de-couverture/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'validation-page-de-couverture',
                            ],
                        ],
                    ],
                    'depot' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/depot/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-depot',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'these' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/these',
                                    'defaults' => [
                                        'action' => 'these',
                                    ],
                                ],
                            ],
                            'these-retraitee' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/these-retraitee',
                                    'defaults' => [
                                        'action' => 'these-retraitee',
                                    ],
                                ],
                            ],
                            'annexes' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/annexes',
                                    'defaults' => [
                                        'action' => 'annexes',
                                    ],
                                ],
                            ],

                            'pv-soutenance' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/pv-soutenance',
                                    'defaults' => [
                                        'action' => 'depot-pv-soutenance',
                                    ],
                                ],
                            ],
                            'rapport-soutenance' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/rapport-soutenance',
                                    'defaults' => [
                                        'action' => 'depot-rapport-soutenance',
                                    ],
                                ],
                            ],
                            'pre-rapport-soutenance' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/pre-rapport-soutenance',
                                    'defaults' => [
                                        'action' => 'depot-pre-rapport-soutenance',
                                    ],
                                ],
                            ],
                            'demande-confident' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/demande-confident',
                                    'defaults' => [
                                        'action' => 'depot-demande-confident',
                                    ],
                                ],
                            ],
                            'prolong-confident' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/prolong-confident',
                                    'defaults' => [
                                        'action' => 'depot-prolong-confident',
                                    ],
                                ],
                            ],
                            'conv-mise-en-ligne' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/conv-mise-en-ligne',
                                    'defaults' => [
                                        'action' => 'depot-conv-mise-en-ligne',
                                    ],
                                ],
                            ],
                            'avenant-conv-mise-en-ligne' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/avenant-conv-mise-en-ligne',
                                    'defaults' => [
                                        'action' => 'depot-avenant-conv-mise-en-ligne',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'depot-divers' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/depot-divers/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-depot-divers',
                                /* @see TheseController::detailDepotDiversAction() */
                            ],
                        ],
                    ],
                    'attestation' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/attestation/:these[/version/:version]',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'attestation',
                            ],
                        ],
                    ],
                    'diffusion' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/diffusion/:these[/version/:version]',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'diffusion',
                            ],
                        ],
                    ],
                    'archivage' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/archivage/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-archivage',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'test-archivabilite' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/test-archivabilite',
                                    'defaults' => [
                                        'action' => 'test-archivabilite',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'archivabilite-these' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/archivabilite-these/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'archivabilite-these',
                            ],
                        ],
                    ],
                    'conformite-these-retraitee' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/conformite-these-retraitee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'conformite-these-retraitee',
                            ],
                        ],
                    ],
                    'rdv-bu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/rdv-bu/:these[/:asynchronous]',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-rdv-bu',
                            ],
                        ],
                    ],
                    'depot-version-corrigee' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/depot-version-corrigee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-depot-version-corrigee',
                            ],
                        ],
                    ],
                    'archivage-version-corrigee' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/archivage-version-corrigee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'detail-archivage-version-corrigee',
                            ],
                        ],
                    ],
                    'validation-these-corrigee' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/validation-these-corrigee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'validation-these-corrigee',
                            ],
                        ],
                    ],
                    'modifier-correction-autorisee-forcee' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-correction-autorisee-forcee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-correction-autorisee-forcee',
                            ],
                        ],
                    ],
                    'modifier-description' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-description/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-description',
                            ],
                        ],
                    ],
                    'modifier-certif-conformite' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-certif-conformite/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-certif-conformite',
                            ],
                        ],
                    ],
                    'modifier-attestation' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-attestation/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-attestation',
                            ],
                        ],
                    ],
                    'modifier-diffusion' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-diffusion/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-diffusion',
                            ],
                        ],
                    ],
                    'modifier-rdv-bu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-rdv-bu/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'modifier-rdv-bu',
                            ],
                        ],
                    ],
                    'points-de-vigilance' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/points-de-vigilance/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'points-de-vigilance',
                            ],
                        ],
                    ],
                    'exporter-convention-mise-en-ligne' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exporter-convention-mise-en-ligne/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\These',
                                'action' => 'exporter-convention-mise-en-ligne',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'notify-date-butoir-correction-depassee' => [
                    'options' => [
                        'route' => 'notify-date-butoir-correction-depassee',
                        'defaults' => [
                            'controller' => 'Application\Controller\TheseObserver',
                            'action' => 'notify-date-butoir-correction-depassee',
                        ],
                    ],
                ],
                'transfer-these-data' => [
                    'options' => [
                        'route' => 'transfer-these-data --source-id= --destination-id=',
                        'defaults' => [
                            'controller' => TheseConsoleController::class,
                            'action' => 'transfer-these-data',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            // DEPTH = 0
            'home' => [
                'pages' => [

                    /**
                     * Annuaire des thèses
                     */
                    // DEPTH = 1
                    'annuaire' => [
                        'order' => -50,
                        'label' => 'Annuaire des thèses',
                        'route' => 'these',
                        'params' => [],
                        'query' => ['etatThese' => 'E'],
                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                        'pages' => [
                            // PAS de pages filles sinon le menu disparaît ! :-/
                        ]
                    ],

                    /**
                     * Navigation pour LA thèse "sélectionnée".
                     */
                    // DEPTH = 1
                    'these_selectionnee' => [
                        'label' => 'Thèse sélectionnée',
                        'route' => 'these/identite',
                        'withtarget' => true,
                        'paramsInject' => [
                            'these',
                        ],
                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                        'pages' => $thesePages = [
                            // DEPTH = 3
                            'identite' => [
                                'label' => 'Fiche',
                                'order' => 10,
                                'route' => 'these/identite',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'glyphicon glyphicon-info-sign',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-identite'),
                                'etape' => null,
                                'visible' => 'Assertion\\These',
                            ],
                            'divider-1' => [
                                'label' => null,
                                'order' => 15,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            //---------------------------------------------------
                            'rapport-activite' => [
                                'id' => 'these-rapport-activite',
                                'label' => "Rapports d'activité",
                                'order' => 20,
                                'route' => 'rapport-activite/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportActiviteController::class, 'consulter'),
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'rapport-csi' => [
                                'label' => 'Rapports CSI',
                                'order' => 30,
                                'route' => 'rapport-csi/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportCsiController::class, 'consulter'),
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'rapport-miparcours' => [
                                'label' => 'Rapports mi-parcours',
                                'order' => 30,
                                'route' => 'rapport-miparcours/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportMiparcoursController::class, 'consulter'),
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'divider-2' => [
                                'label' => null,
                                'order' => 45,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            //---------------------------------------------------
                            'page-rapporteur' => [
                                'order' => 60,
                                'label' => 'Dépôt du rapport',
                                'route' => 'soutenance/index-rapporteur',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-clipboard',
                                'resource' => IndexPrivileges::getResourceId(IndexPrivileges::INDEX_RAPPORTEUR),
                            ],
                            //---------------------------------------------------
                            'depot' => [
                                'label' => 'Dépôt',
                                'order' => 60,
                                'route' => 'these/roadmap',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-file-upload',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'roadmap'),
                                'pages' => [
                                    // DEPTH = 3
                                    'roadmap' => [
                                        'label' => 'Feuille de route',
                                        'route' => 'these/roadmap',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'roadmap',
                                        'icon' => 'glyphicon glyphicon-road',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'roadmap'),
                                        'etape' => null,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'points-de-vigilance' => [
                                        'label' => 'Points de vigilance',
                                        'route' => 'these/points-de-vigilance',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-warning-sign',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'modifier-rdv-bu'),
                                        'etape' => null,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'depot-divers' => [
                                        'id' => 'depot-divers',
                                        'label' => 'Dépôt fichiers divers',
                                        'route' => 'these/depot-divers',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-duplicate',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'divider-these' => [
                                        'label' => null,
                                        'uri' => '',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'divider',
                                        'separator' => true,
                                    ],
                                    'validation-page-de-couverture' => [
                                        'id' => 'validation-page-de-couverture',
                                        'label' => 'Page de couverture',
                                        'route' => 'these/validation-page-de-couverture',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee}',
                                        'icon' => 'glyphicon glyphicon-picture',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'validation-page-de-couverture'),
                                        //'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        //'visible' => 'Assertion\\These',
                                    ],
                                    'depot' => [
                                        'id' => 'depot',
                                        'label' => 'Dépôt de la thèse',
                                        'route' => 'these/depot',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'fas fa-file-upload',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'signalement' => [
                                        'label' => 'Signalement',
                                        'route' => 'these/description',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'glyphicon glyphicon-list-alt',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-description'),
                                        'etape' => WfEtape::CODE_SIGNALEMENT_THESE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'archivage' => [
                                        'label' => 'Archivage',
                                        'route' => 'these/archivage',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'glyphicon glyphicon-folder-open',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-archivage'),
                                        'etape' => WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'rdv-bu' => [
                                        'label' => 'Rendez-vous avec la bibliothèque universitaire',
                                        'route' => 'these/rdv-bu',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'glyphicon glyphicon-calendar',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-rdv-bu'),
                                        'etape' => WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'divider-correction' => [
                                        'label' => null,
                                        'uri' => '',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'divider version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                    ],
                                    'depot-corrigee' => [
                                        'id' => 'depot-corrigee',
                                        'label' => 'Dépôt version corrigée',
                                        'route' => 'these/depot-version-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-duplicate',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'archivage-corrigee' => [
                                        'label' => 'Archivage version corrigée',
                                        'route' => 'these/archivage-version-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-folder-open',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'detail-archivage'),
                                        'etape' => WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'validation-these-corrigee' => [
                                        'label' => 'Validation thèse corrigée',
                                        'route' => 'these/validation-these-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-calendar',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'validation-these-corrigee'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT,
                                        'visible' => 'Assertion\\These',
                                    ],
                                    'modifier-description' => [
                                        'label' => 'Description de la thèse',
                                        'route' => 'these/modifier-description',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        //'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'modifier-description'),
                                        'visible' => false,
                                    ],
                                    'modifier-diffusion' => [
                                        'label' => 'Autorisation de diffusion',
                                        'route' => 'these/modifier-diffusion',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        //'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'modifier-diffusion'),
                                        'visible' => false,
                                    ],
                                    'remise-version-papier-corrigee' => [
                                        'label' => 'Remise exemplaire corrigé',
                                        'route' => 'these/version-papier',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'glyphicon glyphicon-book',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'depot-papier-final'),
                                        'etape' => WfEtape::CODE_REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE,
                                        'visible' => 'Assertion\\These',
                                    ],
                                ]
                            ],
                        ],
                    ],

                    /**
                     * Page pour Doctorant.
                     * Cette page sera dupliquée en 'ma-these-1', 'ma-these-2', etc. automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MA_THESE_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Ma thèse',
                        'route' => 'these/identite',
                        'params' => [
                            'these' => 0,
                        ],
                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                        'pages' => $thesePages,
                    ],

                    /**
                     * Page pour Dir, Codir.
                     * Cette page aura des pages filles 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_THESES_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Mes thèses',
                        'uri' => '',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                        'pages' => [
                            // DEPTH = 2
                            // Déclinée en 'these-1', 'these-2', etc.
                            'THESE' => [
                                'label' => '(Doctorant)',
                                'route' => 'these/identite',
                                'params' => [
                                    'these' => 0,
                                ],
                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                                'pages' => $thesePages,
                            ]
                        ]
                    ],

                    /**
                     * Cette page aura une page fille 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_THESES_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Nos thèses',
                        'route' => 'these/recherche/notres',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                        'pages' => [
                            // DEPTH = 2
                            'THESES' => [
                                'label' => '(Thèses Structure)',
                                'route' => 'these/recherche/notres',
                                'params' => [],
                                'query' => ['etatThese' => 'E'], // injection automatique du filtre "structure"
                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
                            ],
                        ],
                    ],

                ],
            ],
        ],
    ],
    'form_elements' => [
        'invokables' => [
        ],
        'factories' => [
            'MetadonneeTheseForm' => MetadonneeTheseFormFactory::class,
            'AttestationTheseForm' => AttestationTheseFormFactory::class,
            'DiffusionTheseForm' => DiffusionTheseFormFactory::class,
            'RdvBuTheseForm' => RdvBuTheseFormFactory::class,
            'RdvBuTheseDoctorantForm' => RdvBuTheseDoctorantFormFactory::class,
            'PointsDeVigilanceForm' => PointsDeVigilanceFormFactory::class,
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ],
    'hydrators' => array(
        'factories' => array(
            'DiffusionHydrator' => DiffusionHydratorFactory::class,
            'AttestationHydrator' => AttestationHydratorFactory::class,
            'RdvBuHydrator' => RdvBuHydratorFactory::class,
            'PointsDeVigilanceHydrator' => PointsDeVigilanceHydratorFactory::class,
        )
    ),
    'service_manager' => [
        'factories' => [
            ActeurService::class => ActeurServiceFactory::class,
            'TheseService' => TheseServiceFactory::class,
            TheseSearchService::class => TheseSearchServiceFactory::class,
            'TheseObserverService' => TheseObserverServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            TheseAnneeUnivService::class => TheseAnneeUnivServiceFactory::class,
            PageDeCouverturePdfExporter::class => PageDeCouverturePdfExporterFactory::class,
        ],
        'aliases' => [
            TheseService::class => 'TheseService',
        ]
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\These' => TheseControllerFactory::class,
            TheseRechercheController::class => TheseRechercheControllerFactory::class,
            TheseConsoleController::class => TheseConsoleControllerFactory::class,
            'Application\Controller\TheseObserver' => TheseObserverControllerFactory::class,
        ],
        'aliases' => [
            'TheseController' => 'Application\Controller\These',
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            'urlThese' => UrlThesePluginFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'urlThese' => UrlTheseHelperFactory::class,
        ],
    ],

    'message' => [
        'messages' => [
            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::CONFIDENTIALITE_LAIUS,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "La mise en ligne sera ensuite effectuée automatiquement le jour même de l'expiration du délai, sans préavis. " .
                    "Une demande de prolongation auprès du Président de l'université est possible, mais doit anticiper " .
                    "le délai de traitement de la demande." => Diffusion::CONFIDENTIELLE_OUI,
                    "" => Diffusion::CONFIDENTIELLE_NON,
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_DIFFUSION_FORM_LABEL,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "L'auteur autorise la diffusion de sa thèse" => Diffusion::CONFIDENTIELLE_NON,
                    "L'auteur autorise la diffusion de sa thèse à l'issue de la période de confidentialité" => Diffusion::CONFIDENTIELLE_OUI,
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_DIFFUSION_FORM_VALUE,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "Oui, immédiatement" => Diffusion::AUTORISATION_OUI_IMMEDIAT,
                    "Oui, avec embargo après soutenance d'une durée de..." => Diffusion::AUTORISATION_OUI_EMBARGO,
                    "Non" => Diffusion::AUTORISATION_NON,
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_DIFFUSION_FORM_LAIUS,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === null;
                        },

                    "<p>La thèse est consultable sur internet via le portail national des thèses (<a href=\"http://www.theses.fr\">www.theses.fr</a>), " .
                    "sans authentification. La thèse peut également être accessible depuis des plateformes de diffusion choisies par " .
                    "Normandie Université dans le cadre de sa politique de valorisation scientifique " .
                    "(exemple : <a href=\"http://tel.archives-ouvertes.fr\">http://tel.archives-ouvertes.fr</a>). </p>" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT;
                        },

                    "<p>Pendant cette période, la diffusion de la thèse est uniquement assurée dans l’établissement de préparation du Doctorat et au sein de l’ensemble de la communauté universitaire, sans mise en ligne sur internet. Un exemplaire imprimé ou une version numérique en PDF est accessible dans toute bibliothèque, en faisant la demande dans le cadre du prêt entre bibliothèques. La consultation est alors uniquement ouverte à une personne appartenant à la communauté universitaire, avec engagement de ne pas rediffuser la thèse à des tiers non membres de la communauté universitaire.</p>" .
                    "<p>La diffusion en ligne de la thèse est ensuite effectuée automatiquement le lendemain de l’expiration du délai, sans préavis. Une demande de prolongation de l’embargo auprès du service de documentation concerné est possible, mais doit anticiper le délai de traitement de la demande (un mois, hors périodes de fermeture).</p>" =>
                        function (Diffusion $d) {
                            return
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO;
                        },

                    "<p>La diffusion de la thèse est uniquement assurée dans l’établissement de préparation du doctorat et au sein de l’ensemble de la communauté universitaire, sans mise en ligne sur internet. L’auteur peut toutefois revenir sur sa décision à tout moment par avenant à la présente convention.</p>" .
                    "<p>Dans tous les cas, un exemplaire imprimé ou une version numérique en PDF est accessible dans toute bibliothèque, en faisant la demande dans le cadre du prêt entre bibliothèques. La consultation est alors uniquement ouverte à une personne appartenant à la communauté universitaire, avec engagement de ne pas rediffuser la thèse à des tiers non membres de la communauté universitaire.</p>" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_MISE_EN_LIGNE_QUESTION,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "L’auteur autorise la mise en ligne de la version de diffusion de la thèse sur Internet (après, le cas échéant, la fin de la période de confidentialité décidée par l’établissement) :" =>
                        function (Diffusion $d) {
                            return
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT ||
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO;
                        },
                    "L'auteur n'autorise pas la mise en ligne de la version de diffusion de la thèse sur Internet." =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_MISE_EN_LIGNE_REPONSE,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "☑ &nbsp;&nbsp; Oui, immédiatement" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT;
                        },

                    "☑ &nbsp; Oui, avec embargo après soutenance d'une durée de : {duree} " .
                    "<div class='autoris-diffusion-motif-div'>Motif :</div>" .
                    "<div class='autoris-diffusion-motif-div'><p class='autoris-diffusion-motif'>{motif}</p></div>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['duree'] = $d->getAutorisEmbargoDuree();
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO;
                        },

                    "<div class='autoris-diffusion-motif-div'><p>Motif :</p><p class=\"autoris-diffusion-motif\">{motif}</p></div>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_MISE_EN_LIGNE_PHRASE,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "L'auteur autorise la diffusion de sa thèse <em>immédiatement</em>." =>
                        function (Diffusion $d) {
                            return
                                $d->getConfidentielle() === false &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT;
                        },
                    "L'auteur autorise la diffusion de sa thèse <em>avec embargo après soutenance d'une durée de</em> {duree}. <p>Motif : </p>" .
                    "<p class=\"autoris-diffusion-motif pre-scrollable\">{motif}</p>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['duree'] = $d->getAutorisEmbargoDuree();
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return
                                $d->getConfidentielle() === false &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO;
                        },
                    "L'auteur n'autorise <em>pas</em> la diffusion de sa thèse. <p>Motif : </p>" .
                    "<p class=\"autoris-diffusion-motif pre-scrollable\">{motif}</p>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return
                                $d->getConfidentielle() === false &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                    "L'auteur autorise la diffusion de sa thèse <em>immédiatement</em>, à l'issue de la période de confidentialité." =>
                        function (Diffusion $d) {
                            return
                                $d->getConfidentielle() === true &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT;
                        },
                    "L'auteur autorise la diffusion de sa thèse à l'issue de la période de confidentialité <em>avec embargo après soutenance d'une durée de</em> {duree}. <p>Motif : </p>" .
                    "<p class=\"autoris-diffusion-motif pre-scrollable\">{motif}</p>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['duree'] = $d->getAutorisEmbargoDuree();
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return
                                $d->getConfidentielle() === true &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO;
                        },
                    "L'auteur n'autorise <em>pas</em> la diffusion de sa thèse à l'issue de la période de confidentialité. <p>Motif : </p>" .
                    "<p class=\"autoris-diffusion-motif pre-scrollable\">{motif}</p>" =>
                        function (Diffusion $d, array &$sentBackData = []) {
                            $sentBackData['motif'] = $d->getAutorisMotif();
                            return
                                $d->getConfidentielle() === true &&
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::AUTORIS_MISE_EN_LIGNE_LAIUS,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === null;
                        },

                    "<p>La loi applicable à cette présente convention est la loi française. Le tribunal compétent pour juger de tout contentieux lié au présent contrat est le tribunal administratif dans le ressort duquel l'établissement de préparation du doctorat a son siège.</p>" =>
                        function (Diffusion $d) {
                            return $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_IMMEDIAT;
                        },

                    "<p>La thèse est disponible en version imprimée au service de documentation de l’établissement d’accueil et en PDF pour l'ensemble de la communauté universitaire (en intranet ou dans le cadre du Prêt Entre Bibliothèques).</p>" .
                    "<p>La loi applicable à cette présente convention est la loi française. Le tribunal compétent pour juger de tout contentieux lié au présent contrat est le tribunal administratif dans le ressort duquel l'établissement de préparation du doctorat a son siège.</p>" =>
                        function (Diffusion $d) {
                            return
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_OUI_EMBARGO ||
                                $d->getAutorisMel() === (int)Diffusion::AUTORISATION_NON;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::DROITS_AUTEUR_OK_FORM_LABEL,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "L'auteur garantit que tous les documents de la thèse sont libres de droits " .
                    "ou qu'il a les droits afférents pour la reproduction et la représentation sur tous supports" =>
                        true,
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::DROITS_AUTEUR_OK_FORM_VALUE,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "Oui" =>
                        Diffusion::DROIT_AUTEUR_OK_OUI,
                    "Non, à défaut il fournit une version numérique spécifique excluant ces oeuvres tierces (version de diffusion)..." =>
                        Diffusion::DROIT_AUTEUR_OK_NON,
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::DROITS_AUTEUR_OK_PHRASE,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "L'auteur garantit que tous les documents de la thèse sont libres de droits ou " .
                    "qu'il a acquis les droits afférents pour la reproduction et la représentation sur tous supports.</p> " =>
                        function (Diffusion $d) {
                            return $d->getDroitAuteurOk() === true;
                        },
                    "L'auteur ne garantit pas que tous les documents de la thèse sont libres de droits ou " .
                    "qu'il a acquis les droits afférents pour la reproduction et la représentation sur tous supports. <br>" .
                    "À défaut, l'auteur fournit une version numérique spécifique excluant ces oeuvres tierces (version expurgée)." =>
                        function (Diffusion $d) {
                            return $d->getDroitAuteurOk() === false;
                        },
                ],
            ],

            [
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'id' => DiffusionMessages::DROITS_AUTEUR_OK_PHRASE_CONV,
                ////////////////////////////////////////////////////////////////////////////////////////////////////////
                'data' => [
                    "<p>☑ &nbsp;&nbsp; L'auteur garantit que tous les documents de la thèse sont libres de droits ou " .
                    "qu'il a acquis les droits afférents pour la reproduction et la représentation sur tous supports.</p> " .
                    "<p>☐ &nbsp;&nbsp; À défaut, l'auteur déclare fournir en outre lors du dépôt une version numérique " .
                    "spécifique excluant ces oeuvres tierces (version de diffusion).</p>" =>
                        function (Diffusion $d) {
                            return $d->getDroitAuteurOk() === true;
                        },
                    "<p>☐ &nbsp;&nbsp; L'auteur garantit que tous les documents de la thèse sont libres de droits ou " .
                    "qu'il a acquis les droits afférents pour la reproduction et la représentation sur tous supports.</p>" .
                    "<p>☑ &nbsp;&nbsp; À défaut, l'auteur déclare fournir en outre lors du dépôt une version numérique " .
                    "spécifique excluant ces oeuvres tierces (version de diffusion).</p>" =>
                        function (Diffusion $d) {
                            return $d->getDroitAuteurOk() === false;
                        },
                ],
            ],
        ],
    ],
];
