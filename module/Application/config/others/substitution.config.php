<?php

use Application\Controller\Factory\SubstitutionControllerFactory;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Substitution',
                    'action'     => [
                        'index',
                        'selection',
                        'creer',
                        'modifier',
                        'generate-source-input',
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'substitution-index' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/[:language/]substitution/index',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'index',
                        'language'        => 'fr_FR',
                    ],
                ],
            ],
            'substitution-selection' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/selection/:generalisation/:etablissements',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'selection',
                    ],
                ],
            ],
            'substitution-creer' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/creer',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'creer',
                    ],
                ],
            ],
            'substitution-modifier' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/modifier/:cible',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'modifier',
                    ],
                ],
            ],
            'substitution-generer' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/generer/:id',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'generate-source-input',
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
                        'pages' => [
                            'ecole-doctorale' => [
                                'label'    => 'Substitutions',
                                'route'    => 'substitution-index',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [],
        'factories' => [],
        'aliases' => []
    ],
    'controllers'     => [
        'invokables' => [],
        'factories' => [
            'Application\Controller\Substitution' => SubstitutionControllerFactory::class,],
    ],
    'form_elements'   => [
        'invokables' => [],
        'factories' => [],
    ],
    'hydrators' => [
        'invokables' => [],
        'factories' => [],
    ],
    'view_helpers' => [
        'invokables' => [],
        'factories' => [],
    ],
];
