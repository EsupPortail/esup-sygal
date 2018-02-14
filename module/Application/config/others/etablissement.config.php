<?php

use Application\Controller\Factory\EtablissementControllerFactory;
use Application\Form\Factory\EtablissementFormFactory;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Service\Etablissement\EtablissementService;
use Application\View\Helper\EtablissementHelper;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Etablissement',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\Etablissement',
                    'action'     => [
                        'ajouter',
                        'supprimer',
//                        'restaurer',
                        'modifier',
                        'ajouter-individu',
                        'retirer-individu',
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
//                    'restaurer' => [
//                        'type'          => 'Segment',
//                        'options'       => [
//                            'route'       => '/:ecoleDoctorale/restaurer',
//                            'constraints' => [
//                                'ecoleDoctorale' => '\d+',
//                            ],
//                            'defaults'    => [
//                                'action' => 'restaurer',
//                            ],
//                        ],
//                    ],
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
                                'label'    => 'Établissement',
                                'route'    => 'etablissement',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\Etablissement', 'index'),
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
    'view_helpers' => [
        'invokables' => [
            'etab' => EtablissementHelper::class,
        ],
    ],
];
