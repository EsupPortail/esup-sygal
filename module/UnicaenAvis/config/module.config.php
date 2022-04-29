<?php

namespace UnicaenAvis;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Form\AvisFormFactory;
use UnicaenAvis\Hydrator\AvisHydrator;
use UnicaenAvis\Hydrator\AvisHydratorFactory;
use UnicaenAvis\Service\AvisService;
use UnicaenAvis\Service\AvisServiceFactory;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'UnicaenAvis\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/UnicaenAvis/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'UnicaenAvis' => [],
            ],
        ],
        'guards' => [

        ],
    ],
    'router' => [
        'routes' => [
            'unicaen-avis' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/unicaen-avis',
                    'defaults'      => [
//                        'controller' => RapportActiviteController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [

                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [

                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            AvisService::class => AvisServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [

        ],
    ],
    'controller_plugins' => [
        'factories' => [

        ],
    ],
    'hydrators' => [
        'factories' => [
            AvisHydrator::class => AvisHydratorFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            AvisForm::class => AvisFormFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];