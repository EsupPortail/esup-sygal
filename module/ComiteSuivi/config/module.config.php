<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
            ],
        ],
    ],
    'doctrine'     => [
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'ComiteSuivi\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/ComiteSuivi/Entity/Db/Mapping',
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
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'invokables' => [
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
    'view_helpers' => [
        'invokables' => [
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
            '114_' => 'vendor/bootstrap-select-1.13.2/dist/js/bootstrap-select.min.js',
            '150_' => 'js/tinymce/js/tinymce/tinymce.js',
        ],
        'stylesheets' => [
            '050_bootstrap-theme' => '',
            '114_' => 'vendor/bootstrap-select-1.13.2/dist/css/bootstrap-select.min.css',
        ],
    ],
);
