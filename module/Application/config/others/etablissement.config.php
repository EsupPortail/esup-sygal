<?php

use Application\Controller\Factory\EtablissementControllerFactory;
use Application\Form\Factory\EtablissementFormFactory;
use Application\Form\Factory\EtablissementHydratorFactory;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Service\Etablissement\EtablissementService;
use Application\View\Helper\EtablissementHelper;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Etablissement',
                    'action'     => [
                        'index',
                        'information',
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\Etablissement',
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
            'etablissement' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]etablissement',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Etablissement',
                        'action'        => 'index',
                        'language'      => 'fr_FR',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'information' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/information/:etablissement',
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
                            'route'       => '/:etablissement/supprimer',
                            'constraints' => [
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:etablissement/restaurer',
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
                            'route'       => '/:etablissement/modifier',
                            'constraints' => [
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'ajouter-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:etablissement/ajouter-individu',
                            'constraints' => [
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'ajouter-individu',
                            ],
                        ],
                    ],
                    'retirer-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:etablissement/retirer-individu/:etabi',
                            'constraints' => [
                                'etablissement' => '\d+',
                                'etabi' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'retirer-individu',
                            ],
                        ],
                    ],
                    'supprimer-logo' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/supprimer-logo/:etablissement',
                            'constraints' => [
                                'etablissement' => '\d+',
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
                            'etablissement' => [
                                'label'    => 'Établissements',
                                'route'    => 'etablissement',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Etablissement', 'index'),
                                'order'    => 5,
                                'pages' => [
                                    'modification' => [
                                        'label'    => 'Modification',
                                        'route'    => 'etablissement/modifier',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\Etablissement', 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'etablissement',
                                        ],
                                    ],
                                    'information' => [
                                        'label'    => 'Détails',
                                        'route'    => 'etablissement/information',
                                        'resource' => PrivilegeController::getResourceId('Application\Controller\Etablissement', 'index'),

                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'etablissement',
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
            'EtablissementService' => EtablissementService::class,
        ],
        'factories' => [
        ],
        'aliases' => [
            EtablissementService::class => 'EtablissementService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\Etablissement' => EtablissementControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'EtablissementForm' => EtablissementFormFactory::class,
        ],
    ],
    'hydrators' => array(
        'factories' => array(
            'EtablissementHydrator' => EtablissementHydratorFactory::class,
        )
    ),
    'view_helpers' => [
        'invokables' => [
            'etab' => EtablissementHelper::class,
        ],
    ],
];
