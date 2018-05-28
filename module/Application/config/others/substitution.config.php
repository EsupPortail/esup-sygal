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
                        'detruire',
                        'generate-source-input',
                        'substitution-automatique',
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
                    'route'    => '/substitution/creer/:type',
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
            'substitution-detruire' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/detruire/:cible',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'detruire',
                    ],
                ],
            ],
            'substitution-automatique' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/substitution/automatique',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'substitution-automatique',
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
                            'substitution' => [
                                'label'    => 'Substitutions',
                                'route'    => 'substitution-index',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Admin', 'index'),

                                'order'    => 50,
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
