<?php

namespace UnicaenAvis;

use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAvis\Controller\AvisTypeController;
use UnicaenAvis\Controller\AvisTypeControllerFactory;
use UnicaenAvis\Controller\IndexController;
use UnicaenAvis\Controller\IndexControllerFactory;
use UnicaenAvis\Fieldset\AvisTypeFieldset;
use UnicaenAvis\Fieldset\AvisTypeFieldsetFactory;
use UnicaenAvis\Fieldset\AvisValeurFieldset;
use UnicaenAvis\Fieldset\AvisValeurFieldsetFactory;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Form\AvisFormFactory;
use UnicaenAvis\Form\AvisTypeForm;
use UnicaenAvis\Form\AvisTypeFormFactory;
use UnicaenAvis\Hydrator\AvisHydrator;
use UnicaenAvis\Hydrator\AvisHydratorFactory;
use UnicaenAvis\Hydrator\AvisTypeHydrator;
use UnicaenAvis\Hydrator\AvisTypeHydratorFactory;
use UnicaenAvis\Hydrator\AvisValeurHydrator;
use UnicaenAvis\Hydrator\AvisValeurHydratorFactory;
use UnicaenAvis\Provider\Privilege\AvisPrivileges;
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
    'bjyauthorize' => [
        'guards' => [
            'UnicaenPrivilege\Guard\PrivilegeController' => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => AvisPrivileges::AVIS__AVIS_TYPE__AFFICHER,
                ],
                [
                    'controller' => AvisTypeController::class,
                    'action' => [
                        'index',
                        'afficher',
                    ],
                    'privileges' => AvisPrivileges::AVIS__AVIS_TYPE__AFFICHER,
                ],
                [
                    'controller' => AvisTypeController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => AvisPrivileges::AVIS__AVIS_TYPE__AJOUTER,
                ],
                [
                    'controller' => AvisTypeController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => AvisPrivileges::AVIS__AVIS_TYPE__MODIFIER,
                ],
                [
                    'controller' => AvisTypeController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => AvisPrivileges::AVIS__AVIS_TYPE__SUPPRIMER,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'unicaen-avis' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/unicaen-avis',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'avis-type' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/avis-type',
                            'defaults' => [
                                'controller' => AvisTypeController::class,
                                'action' => 'index'
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'ajouter' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/ajouter',
                                    'defaults' => [
                                        'action' => 'ajouter'
                                    ],
                                ],
                            ],
                            'afficher' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/afficher/:avisType',
                                    'defaults' => [
                                        'action' => 'afficher'
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/modifier/:avisType',
                                    'defaults' => [
                                        'action' => 'modifier'
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/supprimer/:avisType',
                                    'defaults' => [
                                        'action' => 'supprimer'
                                    ],
                                ],
                            ],
                        ],
                    ],
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
            IndexController::class => IndexControllerFactory::class,
            AvisTypeController::class => AvisTypeControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [

        ],
    ],
    'hydrators' => [
        'factories' => [
            AvisHydrator::class => AvisHydratorFactory::class,
            AvisTypeHydrator::class => AvisTypeHydratorFactory::class,
            AvisValeurHydrator::class => AvisValeurHydratorFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            AvisForm::class => AvisFormFactory::class,
            AvisTypeForm::class => AvisTypeFormFactory::class,
            AvisTypeFieldset::class => AvisTypeFieldsetFactory::class,
            AvisValeurFieldset::class => AvisValeurFieldsetFactory::class,
//            AvisTypeValeurComplemFieldset::class => AvisTypeValeurComplemFieldsetFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];