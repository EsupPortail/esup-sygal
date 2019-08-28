<?php

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Provider\Privilege\FichierPrivileges;
use Information\Controller\FichierController;
use Information\Controller\InformationController;
use Information\Controller\InformationControllerFactory;
use Information\Form\FichierForm;
use Information\Form\InformationForm;
use Information\Form\InformationFormFactory;
use Information\Form\InformationHydrator;
use Information\Provider\Privilege\InformationPrivileges;
use Information\Service\InformationFichierService;
use Information\Service\InformationFichierServiceFactory;
use Information\Service\InformationService;
use Information\Service\InformationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
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
                [
                    'controller' => FichierController::class,
                    'action'     => [
                        'index',
                        'supprimer',
                    ],
                    'privileges' => [
                        InformationPrivileges::INFORMATION_MODIFIER,
                    ]
                ],
                [
                    'controller' => FichierController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        FichierPrivileges::FICHIER_DIVERS_TELECHARGER,
                    ]
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
                    'fichiers' => [
                        'type'          => Literal::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/fichiers',
                            'defaults'    => [
                                'controller'    => FichierController::class,
                                'action' => 'index',
                            ],
                        ],
                        'child_routes'  => [
                            'supprimer' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/supprimer/:id',
                                    'defaults'    => [
                                        'controller'    => FichierController::class,
                                        'action' => 'supprimer',
                                    ],
                                ],
                            ],
                            'telecharger' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/telecharger/:id',
                                    'defaults'    => [
                                        'controller'    => FichierController::class,
                                        'action' => 'telecharger',
                                    ],
                                ],
                            ],
                        ],
                    ],
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
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'label'    => 'Administration',
                        'route'    => 'admin',
                        'icon'     => 'glyphicon glyphicon-cog',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\Admin', 'index'),
                        'pages' => [
                            'information' => [
                                'order'    => 100,
                                'label'    => 'Pages d\'information',
                                'route'    => 'informations',
                                //'icon'     => 'glyphicon glyphicon-send',
                                'resource' => InformationPrivileges::getResourceId(InformationPrivileges::INFORMATION_MODIFIER),
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'information' => [
            'accueil' => [
                'label' => 'Accueil',
                'route' => 'home',
                'pages' => [
                    'doctorat' => [
                        'label' => 'Le doctorat',
                        'route' => 'informations/afficher',
                        'params' => ['id' => 61],
                        'title' => "Informations sur le doctorat et sa gestion"
                    ],
                    'ecoles-doctorales' => [
                        'label' => 'Les Ecoles Doctorales',
                        'route' => 'informations/afficher',
                        'params' => ['id' => 81],
                        'title' => "Informations sur les Ecoles Doctorales et le Collège des Ecoles doctorales"
                    ],
                    'guide-these' => [
                        'label' => 'Guide de la thèse',
                        'route' => 'informations/afficher',
                        'params' => ['id' => 82],
                        'title' => "Informations sur le déroulement de la thèse et formulaires administratifs à l’intention du doctorant et de ses encadrants"
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            NavigationAbstractServiceFactory::class,
        ],
        'factories' => [
            InformationService::class => InformationServiceFactory::class,
            InformationFichierService::class => InformationFichierServiceFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            InformationController::class => InformationControllerFactory::class,
            FichierController::class => \Information\Controller\FichierControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [
            FichierForm::class => FichierForm::class,
        ],
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