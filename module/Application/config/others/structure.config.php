<?php

use Application\Controller\Factory\EcoleDoctoraleControllerFactory;
use Application\Controller\Factory\StructureControllerFactory;
use Application\Controller\StructureController;
use Application\Form\Factory\EcoleDoctoraleFormFactory;
use Application\Form\Factory\EcoleDoctoraleHydratorFactory;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Provider\Privilege\EtablissementPrivileges;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Structure\StructureService;
use Application\Service\Structure\StructureServiceFactory;
use Application\View\Helper\EcoleDoctoraleHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Application\Entity\Db\StructureConcreteInterface;
use Application\View\Helper\StructureSubstitHelper;
use Application\View\Helper\StructureArrayHelper;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => StructureController::class,
                    'action'     => [
                        'individu-role',
                    ],
                    'privileges' => EtablissementPrivileges::ETABLISSEMENT_CONSULTATION,
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'structure' => [
                'type'          => Segment::class,
                'may_terminate' => false,
                'options'       => [
                    'route'    => '/structure',
                    'defaults' => [
                        'controller'    => StructureController::class,
                    ],
                ],
                'child_routes'  => [
                    'individu-role' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/individu-role/:structure[/:type]',
                            'defaults'    => [
                                'action' => 'individu-role',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
        ],
    ],
    'service_manager' => [
        'factories' => [
            StructureService::class => StructureServiceFactory::class,
        ],
        'aliases' => [
            'StructureService' => StructureService::class,
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            StructureController::class => StructureControllerFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => array(
            'structureSubstitHelper' => StructureSubstitHelper::class,
        ),
        'factories' => [],
    ],
];
