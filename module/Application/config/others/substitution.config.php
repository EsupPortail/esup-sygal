<?php

use Application\Controller\SubstitutionController;
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
                    'route'    => '/substitution/selection',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'selection',
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
        'invokables' => [
            'Application\Controller\Substitution' => SubstitutionController::class,
            ],
        'factories' => [],
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
