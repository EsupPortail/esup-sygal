<?php

namespace Depot;

use Application\Form\Factory\PointsDeVigilanceFormFactory;
use Application\Form\Factory\PointsDeVigilanceHydratorFactory;
use Application\Form\Factory\RdvBuHydratorFactory;
use Application\Form\Factory\RdvBuTheseDoctorantFormFactory;
use Application\Form\Factory\RdvBuTheseFormFactory;
use Application\Navigation\ApplicationNavigationFactory;
use Application\Service\Message\DiffusionMessages;
use Depot\Assertion\These\TheseAssertion;
use Depot\Assertion\These\TheseAssertionFactory;
use Depot\Assertion\These\TheseEntityAssertion;
use Depot\Assertion\These\TheseEntityAssertionFactory;
use Depot\Controller\ConsoleController;
use Depot\Controller\DepotController;
use Depot\Controller\Factory\ConsoleControllerFactory;
use Depot\Controller\Factory\DepotControllerFactory;
use Depot\Controller\Factory\ObserverControllerFactory;
use Depot\Controller\ObserverController;
use Depot\Controller\Plugin\Url\UrlDepotPluginFactory;
use Depot\Entity\Db\Diffusion;
use Depot\Entity\Db\WfEtape;
use Depot\Form\Attestation\AttestationHydratorFactory;
use Depot\Form\Attestation\AttestationTheseFormFactory;
use Depot\Form\Diffusion\DiffusionHydratorFactory;
use Depot\Form\Diffusion\DiffusionTheseFormFactory;
use Depot\Form\Metadonnees\MetadonneeTheseFormFactory;
use Depot\Provider\Privilege\DepotPrivileges;
use Depot\Service\PageDeCouverture\PageDeCouverturePdfExporter;
use Depot\Service\PageDeCouverture\PageDeCouverturePdfExporterFactory;
use Depot\Service\These\DepotService;
use Depot\Service\These\Factory\DepotServiceFactory;
use Depot\Service\Url\UrlDepotService;
use Depot\Service\Url\UrlDepotServiceFactory;
use Depot\View\Helper\Url\UrlDepotHelperFactory;
use Fichier\Entity\Db\NatureFichier;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

$depotFichierDiversRoutesConfig = generateDepotFichierDiversRoutesConfig();

return [
    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            DepotPrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE,
                            DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS,
                            DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE,
                            DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE,
                            DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE,
                            DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE,
                            DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE,
                            DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE,
                            DepotPrivileges::THESE_DEPOT_VERSION_INITIALE,
                            DepotPrivileges::THESE_DEPOT_VERSION_CORRIGEE,
                            DepotPrivileges::THESE_TELECHARGEMENT_FICHIER,
                            DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE,
                            DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE,
                            DepotPrivileges::THESE_SAISIE_RDV_BU,
                            DepotPrivileges::THESE_FICHIER_DIVERS_TELEVERSER,
                            DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                            ThesePrivileges::THESE_CONSULTATION_TOUTES_THESES,
                            ThesePrivileges::THESE_CONSULTATION_SES_THESES,
                            ThesePrivileges::THESE_MODIFICATION_TOUTES_THESES,
                            ThesePrivileges::THESE_MODIFICATION_SES_THESES,
                        ],
                        'resources' => ['These'],
                        'assertion' => TheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'depot-accueil'
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'detail-depot-divers',
                        /* @see TheseController::detailDepotDiversAction() */
                    ],
                    'privileges' => DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'depot-papier-final',
                    ],
                    'privileges' => ThesePrivileges::THESE_RECHERCHE,
                ],
                [
                    'controller' => DepotController::class,
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
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'detail-depot',
                        'detail-depot-version-corrigee',
                        'detail-fichiers',

                        'these',
                        'these-retraitee',
                        'annexes',
                        'attestation',
                        'diffusion',
                        'archivabilite-these',
                        'conformite-these-retraitee',

                        'exporter-convention-mise-en-ligne',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_DEPOT,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => array_map(
                        fn(array $item) => $item['options']['defaults']['action'],
                        $depotFichierDiversRoutesConfig
                    ),
                    'privileges' => DepotPrivileges::THESE_FICHIER_DIVERS_CONSULTER,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'validation-page-de-couverture',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_CONSULTATION_PAGE_COUVERTURE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'detail-description',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_DESCRIPTION,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'detail-archivage',
                        'detail-archivage-version-corrigee',
                        'test-archivabilite',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_ARCHIVAGE,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'detail-rdv-bu',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_RDV_BU,
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-correction-autorisee-forcee',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_CORREC_AUTORISEE_FORCEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'accorder-sursis-correction',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_CORREC_AUTORISEE_ACCORDER_SURSIS,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-description',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_INITIALE,
                        DepotPrivileges::THESE_SAISIE_DESCRIPTION_VERSION_CORRIGEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-attestation',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_INITIALE,
                        DepotPrivileges::THESE_SAISIE_ATTESTATIONS_VERSION_CORRIGEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-diffusion',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_INITIALE,
                        DepotPrivileges::THESE_SAISIE_AUTORISATION_DIFFUSION_VERSION_CORRIGEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-certif-conformite',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_INITIALE,
                        DepotPrivileges::THESE_SAISIE_CONFORMITE_VERSION_ARCHIVAGE_CORRIGEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'modifier-rdv-bu',
                        'points-de-vigilance',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_SAISIE_RDV_BU,
                    ],
                    'assertion' => TheseAssertion::class,
                ],

                [
                    'controller' => DepotController::class,
                    'action' => [
                        'validation-these-corrigee',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => ObserverController::class,
                    'action' => [
                        'notify-date-butoir-correction-depassee',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => DepotController::class,
                    'action' => [
                        'depot-papier-final',
                    ],
                    'privileges' => [
                        DepotPrivileges::THESE_CONSULTATION_VERSION_PAPIER_CORRIGEE,
                    ],
                    'assertion' => TheseAssertion::class,
                ],
                [
                    'controller' => ConsoleController::class,
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
                        'controller' => DepotController::class,
                        'action' => 'depot-accueil',
                    ],
                ],
                'may_terminate' => true,
            ],
            'these' => [
                'child_routes' => [
                    'roadmap' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/roadmap/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => DepotController::class,
                                'action' => 'roadmap',
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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

                            'divers' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/divers',
                                ],
                                'may_terminate' => false,
                                'child_routes' => array_combine(
                                    array_keys($depotFichierDiversRoutesConfig),
                                    $depotFichierDiversRoutesConfig
                                ),
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                /** @see TheseController::accorderSursisCorrectionAction() */
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
                                'action' => 'modifier-correction-autorisee-forcee',
                            ],
                        ],
                    ],
                    'accorder-sursis-correction' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/accorder-sursis-correction/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => DepotController::class,
                                'action' => 'accorder-sursis-correction',
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                                'controller' => DepotController::class,
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
                            'controller' => ObserverController::class,
                            'action' => 'notify-date-butoir-correction-depassee',
                        ],
                    ],
                ],
                'transfer-these-data' => [
                    'options' => [
                        'route' => 'transfer-these-data --source-id= --destination-id=',
                        'defaults' => [
                            'controller' => ConsoleController::class,
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
                     * Navigation pour LA thèse "sélectionnée".
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => $thesePages = [
                            // DEPTH = 3
                            'depot' => [
                                'label' => 'Dépôt de la thèse',
                                'order' => 60,
                                'route' => 'these/roadmap',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-file-upload',
                                'resource' => PrivilegeController::getResourceId(DepotController::class, 'roadmap'),
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
                                        'icon' => 'fas fa-road',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'roadmap'),
                                        'etape' => null,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'points-de-vigilance' => [
                                        'label' => 'Points de vigilance',
                                        'route' => 'these/points-de-vigilance',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-exclamation-triangle',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'modifier-rdv-bu'),
                                        'etape' => null,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'depot-divers' => [
                                        'id' => 'depot-divers',
                                        'label' => 'Dépôt fichiers divers',
                                        'route' => 'these/depot-divers',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-copy',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        'visible' => TheseAssertion::class,
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
                                        'icon' => 'fas fa-image',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'validation-page-de-couverture'),
                                        //'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        //'visible' => TheseAssertion::class,
                                    ],
                                    'depot' => [
                                        'id' => 'depot',
                                        'label' => 'Téléversement',
                                        'route' => 'these/depot',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'fas fa-file-upload',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'signalement' => [
                                        'label' => 'Signalement',
                                        'route' => 'these/description',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'fas fa-file-alt',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-description'),
                                        'etape' => WfEtape::CODE_SIGNALEMENT_THESE,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'archivage' => [
                                        'label' => 'Archivage',
                                        'route' => 'these/archivage',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'fas fa-folder-open',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-archivage'),
                                        'etape' => WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'rdv-bu' => [
                                        'label' => 'Rendez-vous avec la bibliothèque universitaire',
                                        'route' => 'these/rdv-bu',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'class' => 'version-initiale correction-attendue-{correctionAutorisee} correction-effectuee-{correctionEffectuee}',
                                        'icon' => 'fas fa-calendar',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-rdv-bu'),
                                        'etape' => WfEtape::CODE_RDV_BU_SAISIE_DOCTORANT,
                                        'visible' => TheseAssertion::class,
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
                                        'label' => 'Téléversement version corrigée',
                                        'route' => 'these/depot-version-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-copy',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-fichiers'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_ORIGINALE_CORRIGEE,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'archivage-corrigee' => [
                                        'label' => 'Archivage version corrigée',
                                        'route' => 'these/archivage-version-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-folder-open',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'detail-archivage'),
                                        'etape' => WfEtape::CODE_ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'validation-these-corrigee' => [
                                        'label' => 'Validation thèse corrigée',
                                        'route' => 'these/validation-these-corrigee',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-calendar',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'validation-these-corrigee'),
                                        'etape' => WfEtape::CODE_DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT,
                                        'visible' => TheseAssertion::class,
                                    ],
                                    'modifier-description' => [
                                        'label' => 'Description de la thèse',
                                        'route' => 'these/modifier-description',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        //'resource' => PrivilegeController::getResourceId(DepotController::class, 'modifier-description'),
                                        'visible' => false,
                                    ],
                                    'modifier-diffusion' => [
                                        'label' => 'Autorisation de diffusion',
                                        'route' => 'these/modifier-diffusion',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        //'resource' => PrivilegeController::getResourceId(DepotController::class, 'modifier-diffusion'),
                                        'visible' => false,
                                    ],
                                    'remise-version-papier-corrigee' => [
                                        'label' => 'Remise exemplaire corrigé',
                                        'route' => 'these/version-papier',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'icon' => 'fas fa-book',
                                        'resource' => PrivilegeController::getResourceId(DepotController::class, 'depot-papier-final'),
                                        'etape' => WfEtape::CODE_REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE,
                                        'visible' => TheseAssertion::class,
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
                        'pages' => $thesePages,
                    ],

                    /**
                     * Page pour Dir, Codir.
                     * Cette page aura des pages filles 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_THESES_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            // Déclinée en 'these-1', 'these-2', etc.
                            'THESE' => [
                                'pages' => $thesePages,
                            ]
                        ]
                    ],

                ],
            ],
        ],
    ],
    'form_elements' => [
        'factories' => [
            'MetadonneeTheseForm' => MetadonneeTheseFormFactory::class,
            'AttestationTheseForm' => AttestationTheseFormFactory::class,
            'DiffusionTheseForm' => DiffusionTheseFormFactory::class,
            'RdvBuTheseForm' => RdvBuTheseFormFactory::class,
            'RdvBuTheseDoctorantForm' => RdvBuTheseDoctorantFormFactory::class,
            'PointsDeVigilanceForm' => PointsDeVigilanceFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            'DiffusionHydrator' => DiffusionHydratorFactory::class,
            'AttestationHydrator' => AttestationHydratorFactory::class,
            'RdvBuHydrator' => RdvBuHydratorFactory::class,
            'PointsDeVigilanceHydrator' => PointsDeVigilanceHydratorFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            UrlDepotService::class => UrlDepotServiceFactory::class,
            DepotService::class => DepotServiceFactory::class,
            PageDeCouverturePdfExporter::class => PageDeCouverturePdfExporterFactory::class,
            TheseAssertion::class => TheseAssertionFactory::class,
            TheseEntityAssertion::class => TheseEntityAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            DepotController::class => DepotControllerFactory::class,
            ConsoleController::class => ConsoleControllerFactory::class,
            ObserverController::class => ObserverControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'urlDepot' => UrlDepotPluginFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'urlDepot' => UrlDepotHelperFactory::class,
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


function generateDepotFichierDiversRoutesConfig(): array
{
    $config = [];
    foreach (NatureFichier::CODES_FICHIERS_DIVERS as $code) {
        $key = (new NatureFichier())->setCode($code)->getCodeToLowerAndDash();
        $config[$key] = [
            'type' => 'Literal',
            'options' => [
                'route' => '/' . $key,
                'defaults' => [
                    'action' => 'depot-' . $key,
                ],
            ],
        ];
    }

    return $config;
}