<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Assertion\AvisSoutenanceAssertion;
use Soutenance\Assertion\AvisSoutenanceAssertionFactory;
use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Assertion\PropositionAssertion;
use Soutenance\Assertion\PropositionAssertionFactory;
use Soutenance\Controller\AvisSoutenanceController;
use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\Factory\AvisSoutenanceControllerFactory;
use Soutenance\Controller\Factory\ConfigurationControllerFactory;
use Soutenance\Controller\Factory\EngagementImpartialiteControllerFactory;
use Soutenance\Controller\Factory\PresoutenanceControllerFactory;
use Soutenance\Controller\Factory\QualiteControllerFactory;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\QualiteController;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\Avis\AvisForm;
use Soutenance\Form\Avis\AvisFormFactory;
use Soutenance\Form\Avis\AvisHydrator;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\Confidentialite\ConfidentialiteFormFactory;
use Soutenance\Form\Confidentialite\ConfidentialiteHydrator;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Form\Configuration\ConfigurationFormFactory;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisForm;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisFormFactory;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisHydrator;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteEdition\QualiteEditionFormFactory;
use Soutenance\Form\QualiteEdition\QualiteEditiontHydrator;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuFormFactory;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuHydrator;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportForm;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportFormFactory;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportHydrator;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreFormFactory;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreHydrator;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreHydratorFactory;
use Soutenance\Form\SoutenanceRefus\SoutenanceRefusForm;
use Soutenance\Form\SoutenanceRefus\SoutenanceRefusFormFactory;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Provider\Privilege\SoutenancePrivileges;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Avis\AvisServiceFactory;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceFactory;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Parametre\ParametreServiceFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
use Soutenance\Service\Validation\ValidationService;
use Soutenance\Service\Validation\ValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return array(
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
                            SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER,
                            SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_ANNULER,
                            SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                            SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => EngagementImpartialiteAssertion::class,
                    ],
                    [
                        'privileges' => [
                            SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                            SoutenancePrivileges::SOUTENANCE_DATE_RETOUR_MODIFICATION,
                            SoutenancePrivileges::SOUTENANCE_PRESOUTENANCE_VISUALISATION,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => PresoutenanceAssertion::class,
                    ],
                    [
                        'privileges' => [
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ACTEUR,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ED,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_UR,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_BDD,
                            SoutenancePrivileges::SOUTENANCE_PROPOSITION_PRESIDENCE,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => PropositionAssertion::class,
                    ],
                    [
                        'privileges' => [
                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER,
                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_MODIFIER,
                        ],
                        'resources'  => ['Acteur'],
                        'assertion' => AvisSoutenanceAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
                PrivilegeController::class => [
                // Consitution et validations du jury
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'index',
                        'signature-presidence'
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => SoutenanceController::class,
                        'action'     => [
                            'proposition',
                            'avancement',
                        ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'confidentialite',
                        'label-et-anglais',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER,
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                            'valider-acteur',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'valider-structure',
                        'refuser-structure',
                    ],
                    'privileges' => [
                        SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_UR,
                        SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_ED,
                        SoutenancePrivileges::SOUTENANCE_PROPOSITION_VALIDER_BDD,
                    ],
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'add-acteurs',
                        'remove-acteurs',
                        'restore-validation',
                    ],
                    'roles' => [
                        "Administrateur technique",
                    ],
                ],
                // Présoutenance et pages connexes
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'presoutenance',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PRESOUTENANCE_VISUALISATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'date-rendu-rapport',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_DATE_RETOUR_MODIFICATION,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'associer-jury',
                        'deassocier-jury',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'notifier-demande-avis-soutenance',
                        'revoquer-avis-soutenance',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action'     => [
                        'engagement-impartialite',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action'     => [
                        'notifier-rapporteurs-engagement-impartialite',
                        'notifier-engagement-impartialite',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action'     => [
                        'signer-engagement-impartialite',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action'     => [
                        'annuler-engagement-impartialite',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_ANNULER,
                ],
                // Avis de soutenance
                [
                    'controller' => AvisSoutenanceController::class,
                    'action'     => [
                        'index',
                        'afficher',
                        'annuler',
                    ],
                    //'roles' => [],
                    'privileges' => AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER,
                ],
                [
                    'controller' => 'Application\Controller\FichierThese',
                    'action' => [
                        'lister-rapport-presoutenance-by-utilisateur',
                    ],
                    'privileges' => AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER,
                ],
                // Qualite
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                ],
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'editer',
                        'effacer',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],
                [
                    'controller' => ConfigurationController::class,
                    'action'     => [
                        'index',
                    ],
                    'roles' => [ "Administrateur technique" ],
                ],

            ],
        ],
    ],

    'doctrine'     => [
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Soutenance\Entity' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Soutenance/Entity/Mapping',
                ],
            ],
        ],
        'connection'    => [
            'orm_default' => [
                'driver_class' => OCI8::class,
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'soutenance' => [
                        'order'    => -90,
                        'label'    => 'Soutenance',
                        'route'    => 'soutenance',
                        'roles' => [],
                        'pages' => [
                            'proposition' => [
                                'label'    => 'Proposition de soutenance',
                                'route'    => 'soutenance/proposition',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'privileges' => [
                                    SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER,
                                ],
                            ],
                            'presoutenance' => [
                                'label'    => 'Finalisation de la soutenance',
                                'route'    => 'soutenance/presoutenance',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'privileges' => [
                                    SoutenancePrivileges::SOUTENANCE_PRESOUTENANCE_VISUALISATION,
                                ],
                                'pages' => [
                                    'association' => [
                                        'label'    => 'Association d\'un acteur SyGAL',
                                        'route'    => 'soutenance/presoutenance/associer-jury',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                                        ],
                                    ],
                                    'deassociation' => [
                                        'label'    => 'Casser l\'association d\'un acteur SyGAL',
                                        'route'    => 'soutenance/presoutenance/associer-jury',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            SoutenancePrivileges::SOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                                        ],
                                    ],
                                    'engagement' => [
                                        'label'    => 'Engagement d\'impartialité',
                                        'route'    => 'soutenance/presoutenance/engagement-impartialite',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_VISUALISER,
                                        ],
                                    ],
//                                    'avis' => [
//                                        'label'    => 'Avis de soutenance',
//                                        'route'    => 'soutenance/avis-soutenance',
//                                        'withtarget' => true,
//                                        'paramsInject' => [
//                                            'these',
//                                            'rapporteur',
//                                        ],
//                                        'privileges' => [
//                                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER,
//                                        ],
//                                    ],
                                ],
                            ],
                            'qualite' => [
                                'label'    => 'Qualités des membres',
                                'route'    => 'qualite',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'privileges' => [
                                    QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                                ],
                            ],
                            'configuration' => [
                                'label'    => 'Paramétrage du module de soutenance',
                                'route'    => 'configuration',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'roles' => [
                                    "Administrateur technique"
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
//            'add-acteurs' => [
//                'type' => Literal::class,
//                'may_terminate' => true,
//                'options' => [
//                    'route'    => '/add-acteurs',
//                    'defaults' => [
//                        'controller' => SoutenanceController::class,
//                        'action'     => 'add-acteurs',
//                    ],
//                ],
//            ],
//            'remove-acteurs' => [
//                'type' => Literal::class,
//                'may_terminate' => true,
//                'options' => [
//                    'route'    => '/remove-acteurs',
//                    'defaults' => [
//                        'controller' => SoutenanceController::class,
//                        'action'     => 'remove-acteurs',
//                    ],
//                ],
//            ],
//            'restore-validation' => [
//                'type' => Literal::class,
//                'may_terminate' => true,
//                'options' => [
//                    'route'    => '/restore-validation',
//                    'defaults' => [
//                        'controller' => SoutenanceController::class,
//                        'action'     => 'restore-validation',
//                    ],
//                ],
//            ],

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
                            'engagement-impartialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/engagement-impartialite/:membre',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action'     => 'engagement-impartialite',
                                    ],
                                ],
                                'child_routes' => [
                                    'notifier' => [
                                        'type' => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/notifier',
                                            'defaults' => [
                                                'controller' => EngagementImpartialiteController::class,
                                                'action'     => 'notifier-engagement-impartialite',
                                            ],
                                        ],
                                    ],
                                    'signer' => [
                                        'type' => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/signer',
                                            'defaults' => [
                                                'controller' => EngagementImpartialiteController::class,
                                                'action'     => 'signer-engagement-impartialite',
                                            ],
                                        ],
                                    ],
                                    'annuler' => [
                                        'type' => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/annuler',
                                            'defaults' => [
                                                'controller' => EngagementImpartialiteController::class,
                                                'action'     => 'annuler-engagement-impartialite',
                                            ],
                                        ],
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
                    'avancement' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/avancement',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'avancement',
                            ],
                        ],
                        'child_routes' => [],
                    ],
                    'proposition' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/proposition',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'proposition',
                            ],
                        ],
                        'child_routes' => [
                            'signature-presidence' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/signature-presidence',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'signature-presidence',
                                    ],
                                ],
                            ],
                            'modifier-date-lieu' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-date-lieu',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'modifier-date-lieu',
                                    ],
                                ],
                            ],
                            'modifier-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-membre[/:membre]',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'modifier-membre',
                                    ],
                                ],
                            ],
                            'effacer-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/effacer-membre/:membre',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'effacer-membre',
                                    ],
                                ],
                            ],
                            'confidentialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/confidentialite',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'confidentialite',
                                    ],
                                ],
                            ],
                            'label-et-anglais' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/label-et-anglais',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'label-et-anglais',
                                    ],
                                ],
                            ],
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-acteur',
                                    ],
                                ],
                            ],
                            'valider-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider-structure',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-structure',
                                    ],
                                ],
                            ],
                            'refuser-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/refuser-structure',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'refuser-structure',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'avis-soutenance' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/avis-soutenance/:rapporteur',
                            'defaults' => [
                                'controller' => AvisSoutenanceController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'afficher' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/afficher',
                                    'defaults' => [
                                        'controller' => AvisSoutenanceController::class,
                                        'action'     => 'afficher',
                                    ],
                                ],
                            ],
                            'annuler' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/annuler',
                                    'defaults' => [
                                        'controller' => AvisSoutenanceController::class,
                                        'action'     => 'annuler',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'lister-rapport-presoutenance-by-utilisateur' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/lister-rapport-presoutenance-by-utilisateur/:utilisateur',
                            'defaults' => [
                                'controller' => 'Application\Controller\FichierThese',
                                'action'     => 'lister-rapport-presoutenance-by-utilisateur',
                            ],
                        ],
                    ],
                ],
            ],
            'configuration' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/configuration',
                    'defaults' => [
                        'controller' => ConfigurationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'qualite' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/qualite',
                    'defaults' => [
                        'controller' => QualiteController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'editer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/editer[/:qualite]',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'editer',
                            ],
                        ],
                    ],
                    'effacer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/effacer/:qualite',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'effacer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            //service
            PropositionService::class => PropositionServiceFactory::class,
            MembreService::class => MembreServiceFactory::class,
            AvisService::class => AvisServiceFactory::class,
            ParametreService::class => ParametreServiceFactory::class,
            NotifierSoutenanceService::class => NotifierSoutenanceServiceFactory::class,
            ValidationService::class => ValidationServiceFactory::class,
            //assertion
            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
            PresoutenanceAssertion::class => PresoutenanceAssertionFactory::class,
            PropositionAssertion::class => PropositionAssertionFactory::class,
            AvisSoutenanceAssertion::class => AvisSoutenanceAssertionFactory::class,

        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            QualiteController::class => QualiteControllerFactory::class,
            EngagementImpartialiteController::class => EngagementImpartialiteControllerFactory::class,
            PresoutenanceController::class => PresoutenanceControllerFactory::class,
            AvisSoutenanceController::class => AvisSoutenanceControllerFactory::class,
            ConfigurationController::class => ConfigurationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'invokables' => [
//            AvisForm::class => AvisForm::class,
        ],
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
            SoutenanceRefusForm::class => SoutenanceRefusFormFactory::class,
            QualiteEditionForm::class => QualiteEditionFormFactory::class,
            ConfidentialiteForm::class => ConfidentialiteFormFactory::class,
            LabelEtAnglaisForm::class => LabelEtAnglaisFormFactory::class,
            AvisForm::class => AvisFormFactory::class,
            ConfigurationForm::class => ConfigurationFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            SoutenanceDateLieuHydrator::class => SoutenanceDateLieuHydrator::class,
            SoutenanceDateRenduRapportHydrator::class => SoutenanceDateRenduRapportHydrator::class,
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
            ConfidentialiteHydrator::class => ConfidentialiteHydrator::class,
            LabelEtAnglaisHydrator::class => LabelEtAnglaisHydrator::class,
            AvisHydrator::class => AvisHydrator::class,
        ],
        'factories' => [
            SoutenanceMembreHydrator::class => SoutenanceMembreHydratorFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
