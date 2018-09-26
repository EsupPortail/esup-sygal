<?php
//
//
//use Application\Controller\Factory\RoleControllerFactory;
//use Application\Controller\RoleController;
//use UnicaenAuth\Guard\PrivilegeController;
//use Zend\Mvc\Router\Http\Literal;
//use Zend\Mvc\Router\Http\Segment;
//
//return [
//    'bjyauthorize'    => [
//        'guards' => [
//            RoleController::class => [
//                [
//                    'controller' => RoleController::class,
//                    'action'     => [
//                        'index',
//                    ],
//                    'roles' => [],
//                    //'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
//                ],
//            ],
//        ],
//    ],
//
//    'router' => [
//        'routes' => [
//            'role-affichage' => [
//                'type'          => Literal::class,
//                'options'       => [
//                    'route'    => '/role-affichage',
//                    'defaults' => [
//                        'controller'    => RoleController::class,
//                        'action'        => 'index',
//                    ],
//                ],
//                'may_terminate' => true,
//                'child_routes'  => [
//                ],
//            ],
//
//        ],
//    ],
//    'navigation'      => [
//        'default' => [
//            'home' => [
//                'pages' => [
//                    'admin' => [
//                        'pages' => [
//                        ],
//                    ],
//                ],
//            ],
//        ],
//    ],
//    'service_manager' => [
//        'factories' => [],
//    ],
//    'controllers'     => [
//        'factories' => [
//            RoleController::class => RoleControllerFactory::class,
//        ],
//    ],
//    'form_elements'   => [
//        'factories' => [],
//    ],
//    'hydrators' => [
//        'factories' => [],
//    ],
//    'view_helpers' => [
//        'invokables' => [],
//    ],
//];
