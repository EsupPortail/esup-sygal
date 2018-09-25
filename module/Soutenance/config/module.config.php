<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Controller\Factory\PersopassControllerFactory;
use Soutenance\Controller\Factory\QualiteControllerFactory;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\PersopassController;
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
                        ],
                        'resources'  => ['These'],
                        'assertion'  => EngagementImpartialiteAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
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
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'presoutenance',
                    ],
                    'roles'      => [
                        'Administrateur technique',
                        'Observateur COMUE',
                        'Bureau des doctorats UCN',
                    ],
                ],
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'date-rendu-rapport',
                        'demande-expertise',
                        'notifier-demande-expertise',
                        'notifier-demandes-expertise',
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
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'engagement-impartialite',
                        'signer-engagement-impartialite',
                        'annuler-engagement-impartialite',
                    ],
                    'roles' => []

                ],
                [
                    'controller' => PersopassController::class,
                    'action'     => [
                        'afficher',
                        'modifier',
                    ],
                    'roles' => [

                    ],
                ]
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
//                                'class' => 'roadmap',
//                                'icon' => 'glyphicon glyphicon-road',
//                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'roadmap'),
//                                'etape' => null,
//                                'visible' => EngagementImpartialiteAssertion::class,
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
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/soutenance',
                    'defaults' => [
                        'controller' => SoutenanceController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
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
                    'presoutenance' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/presoutenance/:these',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
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
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'date-rendu-rapport',
                                    ],
                                ],
                            ],
                            'demande-expertise' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/demande-expertise/:membre',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'demande-expertise',
                                    ],
                                ],
                            ],
                            'engagement-impartialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/engagement-impartialite/:membre',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'engagement-impartialite',
                                    ],
                                ],
                                'child_routes' => [
                                    'signer' => [
                                        'type' => Segment::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route'    => '/signer',
                                            'defaults' => [
                                                'controller' => SoutenanceController::class,
                                                'action'     => 'signer-engagement-impartialite',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'notifier-demande-expertise' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/notifier-demande-expertise/:membre',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'notifier-demande-expertise',
                                    ],
                                ],
                            ],
                            'notifier-demandes-expertise' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/notifier-demandes-expertise',
                                    'defaults' => [
                                        'controller' => SoutenanceController::class,
                                        'action'     => 'notifier-demandes-expertise',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'persopass' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/persopass/:these',
                            'defaults' => [
                                'controller' => PersopassController::class,
                                'action'     => 'afficher',
                            ],
                        ],
                        'child_routes' => [
                            'modifier' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:membre',
                                    'defaults' => [
                                        'controller' => PersopassController::class,
                                        'action'     => 'modifier',
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
        ],
    ],

    'service_manager' => [
        'factories' => [
            PropositionService::class => PropositionServiceFactory::class,
            MembreService::class => MembreServiceFactory::class,

            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
        ],

    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            PersopassController::class => PersopassControllerFactory::class,
            QualiteController::class => QualiteControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
            SoutenanceRefusForm::class => SoutenanceRefusFormFactory::class,
            PersopassModifierForm::class => PersopassModifierFormFactory::class,
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
