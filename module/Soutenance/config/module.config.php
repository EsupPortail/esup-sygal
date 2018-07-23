<?php


use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuForm;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuFormFactory;
use Soutenance\Form\SoutenanceDateLieu\SoutenanceDateLieuHydrator;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreForm;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreFormFactory;
use Soutenance\Form\SoutenanceMembre\SoutenanceMembreHydrator;
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
                        'index',
                        'constituer',
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                    ],
                    'roles' => [
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
        ],
    ],

    'form_elements' => [
        'factories' => [
            SoutenanceDateLieuForm::class => SoutenanceDateLieuFormFactory::class,
            SoutenanceMembreForm::class => SoutenanceMembreFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            SoutenanceDateLieuHydrator::class => SoutenanceDateLieuHydrator::class,
            SoutenanceMembreHydrator::class => SoutenanceMembreHydrator::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
