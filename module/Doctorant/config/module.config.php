<?php

use Application\Navigation\ApplicationNavigationFactory;
use Doctorant\Controller\DoctorantControllerFactory;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Service\DoctorantService;
use Doctorant\Service\DoctorantServiceFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Doctorant\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Doctorant/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
//        'rule_providers'     => [
//            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [],
//                ],
//            ],
//        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Doctorant',
                    'action' => [
                        'email-contact',
                        'modifier-email-contact',
                    ],
                    'privileges' => DoctorantPrivileges::DOCTORANT_MODIFICATION_PERSOPASS,
//                    'assertion'  => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\Doctorant',
                    'action' => [
                        'consentement',
                    ],
                    'privileges' => DoctorantPrivileges::DOCTORANT_MODIFICATION_PERSOPASS,
//                    'assertion'  => 'Assertion\\These',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'doctorant' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/doctorant/:doctorant',
                    'constraints' => [
                        'doctorant' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => 'Application\Controller\Doctorant',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'email-contact' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/email-contact',
                            'defaults' => [
                                /**
                                 * @see \Doctorant\Controller\DoctorantController::emailContactAction()
                                 */
                                'action' => 'email-contact',
                            ],
                        ],
                    ],
                    'modifier-email-contact' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-email-contact[/:back]',
                            'defaults' => [
                                /**
                                 * @see \Doctorant\Controller\DoctorantController::modifierEmailContactAction()
                                 */
                                'action' => 'modifier-email-contact',
                                'back' => 0
                            ],
                        ],
                    ],
                    'consentement' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/consentement',
                            'defaults' => [
                                /**
                                 * @see \Doctorant\Controller\DoctorantController::consentementAction()
                                 */
                                'action' => 'consentement',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    /**
                     * Page pour Doctorant.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_DONNEES_PAGE_ID => [
                        'order' => -150,
                        'label' => 'Mes données',
                        'route' => 'doctorant/email-contact',
                        //'resource' => PrivilegeController::getResourceId('Doctorant\Controller\Doctorant', 'donnees-perso'),
                        'pages' => [
                            'email-contact' => [
                                'label' => 'Email de contact',
                                'route' => 'doctorant/email-contact',
                                'icon' => 'icon icon-notifier',
                                //'resource' => PrivilegeController::getResourceId('Doctorant\Controller\Doctorant', 'modifier-email-contact'),
                                //'visible' => 'Assertion\\These',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'form_elements' => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
    'hydrators' => array(
        'factories' => array()
    ),
    'service_manager' => [
        'factories' => [
            'DoctorantService' => DoctorantServiceFactory::class,
        ],
        'aliases' => [
            DoctorantService::class => 'DoctorantService',
        ]
    ],
    'controllers' => [
        'invokables' => [],
        'factories' => [
            'Application\Controller\Doctorant' => DoctorantControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlDoctorant' => 'Doctorant\Controller\Plugin\UrlDoctorant',
        ],
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
];
