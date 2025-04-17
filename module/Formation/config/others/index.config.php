<?php

namespace Formation;

use Application\Navigation\ApplicationNavigationFactory;
use Formation\Assertion\Index\IndexAssertion;
use Formation\Assertion\Index\IndexAssertionFactory;
use Formation\Controller\IndexController;
use Formation\Controller\IndexControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_AFFICHER,
                    ],
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-doctorant',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_DOCTORANT,
                    ],
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-formateur',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_FORMATEUR,
                    ],
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-maison-doctorat',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_MAISON_DOCTORAT,
                    ],
                    'assertion' => IndexAssertion::class
                ],
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    ApplicationNavigationFactory::FORMATIONS_PAGE_ID => [
                        'label'    => 'Formations',
                        'route'    => 'formation',
//                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index') ,
                        'order'    => 90,
                    ],
                    ApplicationNavigationFactory::MES_FORMATIONS_DOCTORANT_PAGE_ID => [
                        'label'    => 'Mes formations',
                        'route'    => 'formation/index-doctorant',
//                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-doctorant') ,
                        'order'    => 91,
                    ],
                    ApplicationNavigationFactory::MES_FORMATIONS_FORMATEUR_PAGE_ID => [
                        'label'    => 'Mes formations',
                        'route'    => 'formation/index-formateur',
//                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-formateur') ,
                        'order'    => 92,
                    ],
                    /**
                     * Navigation pour LA thèse "sélectionnée".
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 3
                            'divider-formation' => [
                                'label' => null,
                                'order' => 35,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'index-maison-doctorat' => [
                                'id' => 'index-maison-doctorat',
                                'label' => 'Formation',
                                'order' => 36,
                                'route' => 'formation/index-maison-doctorat',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                            'icon' => 'fas fa-book-open',
                            'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-maison-doctorat'),
                            'visible' => IndexAssertion::class,
                            ],
                        ]
                    ]
                ]
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'type'  => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/formation',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'index-doctorant' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/index-doctorant[/:doctorant]',
                            'defaults' => [
                                'controller' => IndexController::class,
                                /** @see IndexController::indexDoctorantAction() */
                                'action'     => 'index-doctorant',
                            ],
                        ],
                    ],
                    'index-formateur' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/index-formateur[/:formateur]',
                            'defaults' => [
                                'controller' => IndexController::class,
                                /** @see IndexController::indexFormateurAction() */
                                'action'     => 'index-formateur',
                            ],
                        ],
                    ],
                    'index-maison-doctorat' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/index-maison-doctorat/:these',
                            'defaults' => [
                                'controller' => IndexController::class,
                                /** @see IndexController::indexMaisonDoctoratAction() */
                                'action'     => 'index-maison-doctorat',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            IndexAssertion::class => IndexAssertionFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];