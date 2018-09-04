<?php

use Application\Controller\Factory\EcoleDoctoraleControllerFactory;
use Application\Form\Factory\EcoleDoctoraleFormFactory;
use Application\Form\Factory\EcoleDoctoraleHydratorFactory;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\View\Helper\EcoleDoctoraleHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\EcoleDoctorale',
                    'action'     => [
                        'index',
                        'information'
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\EcoleDoctorale',
                    'action'     => [
                        'ajouter',
                        'supprimer',
                        'restaurer',
                        'modifier',
                        'ajouter-individu',
                        'retirer-individu',
                        'supprimer-logo',
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_MODIFICATION,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'ecole-doctorale' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]ecole-doctorale',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'EcoleDoctorale',
                        'action'        => 'index',
                        'language'      => 'fr_FR',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'information' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/information/:ecoleDoctorale',
                            'defaults'    => [
                                'action' => 'information',
                            ],
                        ],

                    ],
                    'ajouter' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/ajouter',
                            'defaults'    => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:ecoleDoctorale/supprimer',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:ecoleDoctorale/restaurer',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:ecoleDoctorale/modifier',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'ajouter-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:ecoleDoctorale/ajouter-individu',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'ajouter-individu',
                            ],
                        ],
                    ],
                    'retirer-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:ecoleDoctorale/retirer-individu/:edi',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                                'edi' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'retirer-individu',
                            ],
                        ],
                    ],
                    'supprimer-logo' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/supprimer-logo/:ecoleDoctorale',
                            'constraints' => [
                                'ecoleDoctorale' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer-logo',
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
                            'ecole-doctorale' => [
                                'label'    => 'Écoles doctorales',
                                'route'    => 'ecole-doctorale',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),

                                'order'    => 10,
                                'pages' => [
                                    'modification' => [
                                        'label'    => 'Modification',
                                        'route'    => 'ecole-doctorale/modifier',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'ecoleDoctorale',
                                        ],
                                    ],
                                    'information' => [
                                        'label'    => 'Détails',
                                        'route'    => 'ecole-doctorale/information',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'ecoleDoctorale',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'EcoleDoctoraleService' => EcoleDoctoraleService::class,
        ],
        'factories' => [
        ],
        'aliases' => [
            EcoleDoctoraleService::class => 'EcoleDoctoraleService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\EcoleDoctorale' => EcoleDoctoraleControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'EcoleDoctoraleForm' => EcoleDoctoraleFormFactory::class,
        ],
    ],
    'hydrators' => array(
        'factories' => array(
            'EcoleDoctoraleHydrator' => EcoleDoctoraleHydratorFactory::class,
        )
    ),
    'view_helpers' => [
        'invokables' => [
            'ed' => EcoleDoctoraleHelper::class,
        ],
    ],
];
