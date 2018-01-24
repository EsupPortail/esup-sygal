<?php

use Application\Service\ServiceAwareInitializer;
use Retraitement\Service\RetraitementServiceFactory;

return array(
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
//                'These' => [],
            ],
        ],
        'rule_providers'     => [
//            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [
//                        'privileges' => [
//                            Privileges::THESE_CONSULTATION,
//                            Privileges::THESE_SAISIE_DESCRIPTION,
//                            Privileges::THESE_SAISIE_AUTORISATION_DIFFUSION,
//                            Privileges::THESE_DEPOT_FICHIER,
////                            Privileges::THESE_SAISIE_MOT_CLE_RAMEAU,
//                            Privileges::THESE_SAISIE_CONFORMITE_ARCHIVAGE,
//                        ],
//                        'resources'  => ['These'],
//                        'assertion'  => 'Assertion\\These',
//                    ],
//                ],
//            ],
        ],
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => 'Retraitement\Controller\Index',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => \Application\Provider\Privilege\ThesePrivileges::THESE_RECHERCHE,
                ],
            ],
        ],
    ],
    'router' => array(
        'routes' => array(
//            'home' => array(
//                'type' => 'Literal',
//                'options' => array(
//                    'route'    => '/',
//                    'defaults' => array(
//                        'controller' => 'Application\Controller\Index', // <-- change here
//                        'action'     => 'index',
//                    ),
//                ),
//            ),
//            'accueil-doctorant' => array(
//                'type' => 'Literal',
//                'options' => array(
//                    'route'    => '/accueil/doctorant',
//                    'defaults' => array(
//                        'controller' => 'Application\Controller\Index',
//                        'action'     => 'index-doctorant',
//                    ),
//                ),
//            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/retraitement',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Retraitement\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
//                    'droits' => [
//                        'order' => -80,
//                    ],
                ],
            ],
        ],
    ],
    'service_manager' => array(
        'invokables' => array(

        ),
        'factories' => array(
            'RetraitementService' => RetraitementServiceFactory::class,
        ),
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ),

    'controllers' => array(
        'invokables' => array(
            'Retraitement\Controller\Index' => 'Retraitement\Controller\IndexController',
        ),
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);