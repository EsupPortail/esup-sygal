<?php

use Application\Controller\Factory\UniteRechercheControllerFactory;
use Application\Form\Factory\UniteRechercheFormFactory;
use Application\Form\Factory\UniteRechercheHydratorFactory;
use Application\Provider\Privilege\UniteRecherchePrivileges;
use Application\Service\DomaineScientifiqueService;
use Application\Service\UniteRecherche\UniteRechercheService;
use UnicaenAuth\Guard\PrivilegeController;
use Application\View\Helper\UniteRechercheHelper;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\UniteRecherche',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => UniteRecherchePrivileges::UNITE_RECH_CONSULTATION,
                ],
                [
                    'controller' => 'Application\Controller\UniteRecherche',
                    'action'     => [
                        'ajouter',
                        'supprimer',
                        'restaurer',
                        'modifier',
                        'ajouter-individu',
                        'retirer-individu',
                        'supprimer-logo',
                        'ajouter-etablissement-rattachement',
                        'retirer-etablissement-rattachement',
                        'principal-etablissement-rattachement',
                    ],
                    'privileges' => UniteRecherchePrivileges::UNITE_RECH_MODIFICATION,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'unite-recherche' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]unite-recherche',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'UniteRecherche',
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
                            'route'       => '/:uniteRecherche/supprimer',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/restaurer',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/modifier',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'ajouter-etablissement-rattachement' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/ajouter-etablissement-rattachement/:etablissement',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'ajouter-etablissement-rattachement',
                            ],
                        ],
                    ],
                    'retirer-etablissement-rattachement' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/retirer-etablissement-rattachement/:etablissement',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'retirer-etablissement-rattachement',
                            ],
                        ],
                    ],
                    'principal-etablissement-rattachement' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/principal-etablissement-rattachement/:etablissement',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'principal-etablissement-rattachement',
                            ],
                        ],
                    ],
                    'ajouter-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/ajouter-individu',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'ajouter-individu',
                            ],
                        ],
                    ],
                    'retirer-individu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/:uniteRecherche/retirer-individu/:edi',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
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
                            'route'       => '/supprimer-logo/:uniteRecherche',
                            'constraints' => [
                                'uniteRecherche' => '\d+',
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
                            'unite-recherche' => [
                                'label'    => 'Unités de recherche',
                                'route'    => 'unite-recherche',
                                'resource' => PrivilegeController::getResourceId('Application\Controller\UniteRecherche', 'index'),

                                'order'    => 20,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'UniteRechercheService' => UniteRechercheService::class,
            DomaineScientifiqueService::class => DomaineScientifiqueService::class,
        ],
        'factories' => [
        ],
        'aliases' => [
            UniteRechercheService::class => 'UniteRechercheService',
        ]
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            'Application\Controller\UniteRecherche' => UniteRechercheControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [
        ],
        'factories' => [
            'UniteRechercheForm' => UniteRechercheFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            'UniteRechercheHydrator' => UniteRechercheHydratorFactory::class,
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'ur' => UniteRechercheHelper::class,
        ],
    ],
];
