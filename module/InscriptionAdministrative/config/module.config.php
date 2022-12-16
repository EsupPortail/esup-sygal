<?php

namespace InscriptionAdministrative;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use InscriptionAdministrative\Controller\IndexController;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'InscriptionAdministrative\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/InscriptionAdministrative/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'inscription-administrative' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/inscription-administrative',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [

                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'inscription-administrative' => [
                                'label' => 'Inscriptions administratives',
                                'route' => 'inscription-administrative',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index'),
                                'order' => 200,
                                'pages' => [

                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    /**
                     * @see \InscriptionAdministrative\Controller\IndexController::indexAction()
                     */
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'role' => [],
                    //'privileges' => InscriptionAdministrativePrivileges::LOG_LISTER,
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [

        ],
    ],
    'controllers' => [
        'factories' => [

        ],
    ],
    'form_elements' => [
        'invokables' => [

        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];