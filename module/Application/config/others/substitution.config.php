<?php

use Application\Controller\Factory\SubstitutionControllerFactory;
use Application\Provider\Privilege\StructurePrivileges;
use Application\Provider\Privilege\SubstitutionPrivileges;
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
                        'index-structure',
                        'selection',
                        'creer',
                        'modifier',
                        'detruire',
                        'generate-source-input',
                        'substitution-automatique',
                        'modifier-automatique',
                        'enregistrer-automatique',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
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
            'substitution-index-structure' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/[:language/]substitution/index-structure/:type',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Substitution',
                        'action'        => 'index-structure',
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
                'may_terminate' => true,
                'child_routes'  => [
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/modifier/:type/:identifiant',
                            'defaults'    => [
                                'action' => 'modifier-automatique',
                            ],
                        ],
                    ],
                    'enregistrer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/enregistrer/:type/:identifiant',
                            'defaults'    => [
                                'action' => 'enregistrer-automatique',
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
                        'pages' => [
                            'substitution' => [
                                'label'    => 'Substitutions',
                                'route'    => 'substitution-index',
//                                'resources' => [
//                                    SubstitutionPrivileges::getResourceId(SubstitutionPrivileges::SUBSTITUTION_CONSULTATION_TOUTES_STRUCTURES),
//                                    SubstitutionPrivileges::getResourceId(SubstitutionPrivileges::SUBSTITUTION_CONSULTATION_SA_STRUCTURE),
//                                ],
                                'resource' => SubstitutionPrivileges::getResourceId(SubstitutionPrivileges::SUBSTITUTION_CONSULTATION_TOUTES_STRUCTURES),

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
