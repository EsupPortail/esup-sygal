<?php

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Information\Controller\InformationController;
use Information\Controller\InformationControllerFactory;
use Information\Form\InformationForm;
use Information\Form\InformationFormFactory;
use Information\Form\InformationHydrator;
use Information\Provider\Privilege\InformationPrivileges;
use Information\Service\InformationService;
use Information\Service\InformationServiceFactory;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use Zend\Navigation\Service\NavigationAbstractServiceFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

return [
    'doctrine'     => [
        /**
         * Génération du mapping à partir de la bdd, exemple :
         *   $ vendor/bin/doctrine-module orm:convert-mapping --namespace="Application\\Entity\\Db\\" --filter="Version" --from-database xml module/Application/src/Application/Entity/Db/Mapping
         *
         * Génération des classes d'entité, exemple :
         *   $ vendor/bin/doctrine-module orm:generate:entities --filter="Version" module/Application/src
         */
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Information\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Information/Entity/Db/Mapping',
                ],
            ],
        ],
        'eventmanager'  => [
            'orm_default' => [
                'subscribers' => [
                    'UnicaenApp\HistoriqueListener',
                ],
            ],
        ],
        'connection'    => [
            'orm_default' => [
                'driver_class' => OCI8::class,
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'default_repository_class_name' => DefaultEntityRepository::class,
            ]
        ],
        'cache' => [
            'memcached' => [
                'namespace' => 'Sygal_Doctrine',
                'instance'  => 'Sygal\Memcached',
            ],
        ],
    ],
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        InformationPrivileges::INFORMATION_MODIFIER,
                    ]
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'ajouter',
                        'supprimer',
                    ],
                    'privileges' => [
                        InformationPrivileges::INFORMATION_MODIFIER,
                    ]
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'modifier',
                    ],
                    'privileges' => [
                        InformationPrivileges::INFORMATION_MODIFIER,
                    ]
                ],
                [
                    'controller' => InformationController::class,
                    'action'     => [
                        'afficher',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'informations' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/informations',
                    'defaults' => [
                        'controller'    => InformationController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'afficher' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/afficher/:id',
                            'defaults'    => [
                                'action' => 'afficher',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer/:id',
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/modifier/:id',
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'information' => [
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            NavigationAbstractServiceFactory::class,
        ],
        'factories' => [
            InformationService::class => InformationServiceFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            InformationController::class => InformationControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            InformationForm::class => InformationFormFactory::class,
        ],
    ],
    'hydrators' => [
        'invokables' => [
            InformationHydrator::class => InformationHydrator::class
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
            '902_' => 'js/tinymce/js/tinymce/tinymce.js',
        ],
    ]
];