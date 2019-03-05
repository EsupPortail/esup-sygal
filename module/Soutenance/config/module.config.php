<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Assertion\AvisSoutenanceAssertion;
use Soutenance\Assertion\AvisSoutenanceAssertionFactory;
use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Assertion\PresoutenanceAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
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
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Form\Configuration\ConfigurationFormFactory;
use Soutenance\Form\QualiteEdition\QualiteEditionForm;
use Soutenance\Form\QualiteEdition\QualiteEditionFormFactory;
use Soutenance\Form\QualiteEdition\QualiteEditiontHydrator;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportForm;
use Soutenance\Form\SoutenanceDateRenduRapport\SoutenanceDateRenduRapportFormFactory;
use Soutenance\Provider\Privilege\AvisSoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
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
                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_VISUALISER,
                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_MODIFIER,
                            AvisSoutenancePrivileges::SOUTENANCE_AVIS_ANNULER,
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
                            'avancement',
                        ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
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
                    ],
                    'privileges' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                ],
                [
                    'controller' => PresoutenanceController::class,
                    'action'     => [
                        'revoquer-avis-soutenance'
                    ],
                    'privileges' => AvisSoutenancePrivileges::SOUTENANCE_AVIS_ANNULER,
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
                [
                    'controller' => AvisSoutenanceController::class,
                    'action'     => [
                        'annuler',
                    ],
                    'privileges' => AvisSoutenancePrivileges::SOUTENANCE_AVIS_ANNULER,
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
                                    PropositionPrivileges::PROPOSITION_VISUALISER,
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
            MembreService::class => MembreServiceFactory::class,
            AvisService::class => AvisServiceFactory::class,
            ParametreService::class => ParametreServiceFactory::class,
            NotifierSoutenanceService::class => NotifierSoutenanceServiceFactory::class,
            ValidationService::class => ValidationServiceFactory::class,
            //assertion
            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
            PresoutenanceAssertion::class => PresoutenanceAssertionFactory::class,
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
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            QualiteEditionForm::class => QualiteEditionFormFactory::class,
            AvisForm::class => AvisFormFactory::class,
            ConfigurationForm::class => ConfigurationFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
            AvisHydrator::class => AvisHydrator::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
