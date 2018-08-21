<?php


use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\Factory\PersopassControllerFactory;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\PersopassController;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\PersopassModifier\PersopassModifierForm;
use Soutenance\Form\PersopassModifier\PersopassModifierFormFactory;
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
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'presoutenance',
                        'date-rendu-rapport',
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
        ],

    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            PersopassController::class => PersopassControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            SoutenanceDateRenduRapportForm::class => SoutenanceDateRenduRapportFormFactory::class,
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
            SoutenanceRefusForm::class => SoutenanceRefusFormFactory::class,
            PersopassModifierForm::class => PersopassModifierFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            SoutenanceDateLieuHydrator::class => SoutenanceDateLieuHydrator::class,
            SoutenanceDateRenduRapportHydrator::class => SoutenanceDateRenduRapportHydrator::class,
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
