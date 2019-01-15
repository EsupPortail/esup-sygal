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
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\Factory\AvisSoutenanceControllerFactory;
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
use Soutenance\Form\Cotutelle\CotutelleForm;
use Soutenance\Form\Cotutelle\CotutelleHydrator;
use Soutenance\Form\Cotutelle\CotutelleFormFactory;
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
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
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
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => SoutenanceController::class,
                        'action'     => [
                            'proposition',
                        ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'cotutelle',
                        'confidentialite',
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_PROPOSITION_MODIFIER,
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                            'valider',
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
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
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
                        ],
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
                            'cotutelle' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/cotutelle',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'cotutelle',
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
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider',
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
            //assertion
            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
            PresoutenanceAssertion::class => PresoutenanceAssertionFactory::class,
            PropositionAssertion::class => PropositionAssertionFactory::class,
            AvisSoutenanceAssertion::class => AvisSoutenanceAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            QualiteController::class => QualiteControllerFactory::class,
            EngagementImpartialiteController::class => EngagementImpartialiteControllerFactory::class,
            PresoutenanceController::class => PresoutenanceControllerFactory::class,
            AvisSoutenanceController::class => AvisSoutenanceControllerFactory::class
        ],
    ],

    'form_elements' => [
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
            SoutenanceRefusForm::class => SoutenanceRefusFormFactory::class,
            QualiteEditionForm::class => QualiteEditionFormFactory::class,
            CotutelleForm::class => CotutelleFormFactory::class,
            ConfidentialiteForm::class => ConfidentialiteFormFactory::class,
            AvisForm::class => AvisFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            SoutenanceDateLieuHydrator::class => SoutenanceDateLieuHydrator::class,
            SoutenanceDateRenduRapportHydrator::class => SoutenanceDateRenduRapportHydrator::class,
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
            CotutelleHydrator::class => CotutelleHydrator::class,
            ConfidentialiteHydrator::class => ConfidentialiteHydrator::class,
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
