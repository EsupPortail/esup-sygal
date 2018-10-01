<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Assertion\PresoutenanceIndividuAssertion;
use Soutenance\Assertion\PresoutenanceAssertionFactory;
use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\Factory\EngagementImpartialiteControllerFactory;
use Soutenance\Controller\Factory\PersopassControllerFactory;
use Soutenance\Controller\Factory\PresoutenanceControllerFactory;
use Soutenance\Controller\Factory\QualiteControllerFactory;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\PersopassController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Controller\QualiteController;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\PersopassModifier\PersopassModifierForm;
use Soutenance\Form\PersopassModifier\PersopassModifierFormFactory;
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
use Soutenance\Provider\Privilege\SoutenancePrivileges;
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
                        'assertion'  => PresoutenanceIndividuAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
                PrivilegeController::class => [

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
                        'associer-membre-individu',
                        'enregistrer-association-membre-individu',
                        'rechercher-acteur',
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
                // autres bazars
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'index',
                        'constituer',
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'valider',
                        'refuser',
                        'valider-ur',
                        'valider-ur-validation',
                        'valider-ur-refus',
                        'valider-ed',
                        'valider-ed-validation',
                        'valider-ed-refus',

                    ],
                    'roles' => [
                    ],
                ],
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'index',
                        'editer',
                        'effacer',
                    ],
                    'roles'      => [
                        'Administrateur technique',
                    ],
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
                        'order'    => -100,
                        'label'    => 'Soutenance',
                        'route'    => 'soutenance',
                        'privilege' => SoutenancePrivileges::SOUTENANCE_ENGAGEMENT_IMPARTIALITE_SIGNER,
                        'pages' => [
                            'signer' => [
                                'label'    => 'Signer le ererze',
                                'route'    => 'soutenance/presoutenance/engagement-impartialite',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
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
                'may_terminate' => false,
                'options' => [
                    'route'    => '/soutenance/:these',
//                    'defaults' => [
//                        'controller' => SoutenanceController::class,
//                        'action'     => 'index',
//                    ],
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
                            'associer-membre-individu' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/associer-membre-individu/:membre',
                                    'defaults' => [
                                        'controller' => PresoutenanceController::class,
                                        'action'     => 'associer-membre-individu',
                                    ],
                                ],
                                'child_routes' => [
                                    'rechercher-acteur' => [
                                        'type'          => Segment::class,
                                        'options'       => [
                                            'route'       => '/rechercher-acteur',
                                            'defaults'    => [
                                                'action' => 'rechercher-acteur',
                                            ],
                                        ],
                                    ],
                                    'enregistrer' => [
                                        'type' => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/enregistrer/:acteur',
                                            'defaults' => [
                                                'controller' => PresoutenanceController::class,
                                                'action'     => 'enregistrer-association-membre-individu',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'constituer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/constituer/:these',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'constituer',
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
                            'refuser' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/refuser',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'refuser',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'valider-ur' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/valider-ur/:these',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'valider-ur',
                            ],
                        ],
                        'child_routes' => [
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-ur-validation',
                                    ],
                                ],
                            ],
                            'refuser' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/refuser',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-ur-refus',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'valider-ed' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/valider-ed/:these',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'valider-ed',
                            ],
                        ],
                        'child_routes' => [
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-ed-validation',
                                    ],
                                ],
                            ],
                            'refuser' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/refuser',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'valider-ed-refus',
                                    ],
                                ],
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
            //assertion
            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
            PresoutenanceIndividuAssertion::class => PresoutenanceAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            QualiteController::class => QualiteControllerFactory::class,
            EngagementImpartialiteController::class => EngagementImpartialiteControllerFactory::class,
            PresoutenanceController::class => PresoutenanceControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
            SoutenanceRefusForm::class => SoutenanceRefusFormFactory::class,
            QualiteEditionForm::class => QualiteEditionFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            SoutenanceDateLieuHydrator::class => SoutenanceDateLieuHydrator::class,
            SoutenanceDateRenduRapportHydrator::class => SoutenanceDateRenduRapportHydrator::class,
            QualiteEditiontHydrator::class => QualiteEditiontHydrator::class,
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
