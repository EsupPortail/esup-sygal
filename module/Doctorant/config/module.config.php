<?php

namespace Doctorant;

use Doctorant\Assertion\DoctorantAssertion;
use Doctorant\Assertion\DoctorantAssertionFactory;
use Doctorant\Assertion\These\TheseAssertion;
use Doctorant\Assertion\These\TheseAssertionFactory;
use Doctorant\Assertion\These\TheseEntityAssertion;
use Doctorant\Assertion\These\TheseEntityAssertionFactory;
use Doctorant\Controller\DoctorantController;
use Doctorant\Controller\DoctorantControllerFactory;
use Doctorant\Controller\MissionEnseignementController;
use Doctorant\Controller\MissionEnseignementControllerFactory;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Form\MailConsentementFormFactory;
use Doctorant\Form\MissionEnseignement\MissionEnseignementForm;
use Doctorant\Form\MissionEnseignement\MissionEnseignementFormFactory;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydrator;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydratorFactory;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Provider\Privilege\MissionEnseignementPrivileges;
use Doctorant\Service\DoctorantService;
use Doctorant\Service\DoctorantServiceFactory;
use Doctorant\Service\Search\DoctorantSearchService;
use Doctorant\Service\Search\DoctorantSearchServiceFactory;
use Doctorant\Service\MissionEnseignement\MissionEnseignementService;
use Doctorant\Service\MissionEnseignement\MissionEnseignementServiceFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use These\Provider\Privilege\CoEncadrantPrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

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
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT,
                            DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT,
                        ],
                        'resources' => ['These'],
                        'assertion' => TheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'modifier-email-contact',
                        'modifier-email-contact-consent',
                    ],
                    'privileges' => DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT,
                    'assertion'  => TheseAssertion::class,
                ],
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'voir',
                        'consulter',
                    ],
                    'privilege' => [
                        DoctorantPrivileges::DOCTORANT_CONSULTER_TOUT,
                        DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN,
                    ],
                    'assertion'  => DoctorantAssertion::class,
                ],
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'rechercher',
                        'lister',
                    ],
                    'privilege' => [
                        DoctorantPrivileges::DOCTORANT_LISTER_TOUT,
                        DoctorantPrivileges::DOCTORANT_LISTER_SIEN,
                    ],
                    'assertion'  => DoctorantAssertion::class,
                ],
                [
                    'controller' => MissionEnseignementController::class,
                    'action' => [
                        'ajouter',
                        'retirer',
                    ],
                    'privileges' => MissionEnseignementPrivileges::MISSION_ENSEIGNEMENT_MODIFIER,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'recherche-doctorant' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/recherche-doctorant',
                    'defaults' => [
                        'controller' => DoctorantController::class,
                        'action' => 'rechercher',
                    ],
                ],
                'may_terminate' => true,
            ],
            'doctorant' => [
                'type' => LIteral::class,
                'options' => [
                    'route' => '/doctorant',
                    'defaults' => [
                        'controller' => DoctorantController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'voir' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/voir/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/restaurer/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter',
                            'defaults' => [
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'rechercher' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/rechercher',
                            'defaults' => [
                                'controller' => DoctorantController::class,
                                'action' => 'rechercher',
                            ],
                        ],
                    ],
                    'modifier-email-contact' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-email-contact/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                /**
                                 * @see \Doctorant\Controller\DoctorantController::modifierEmailContactAction()
                                 */
                                'action' => 'modifier-email-contact',
                            ],
                        ],
                    ],
                    'modifier-email-contact-consent' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-email-contact-consent/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                /**
                                 * @see \Doctorant\Controller\DoctorantController::modifierEmailContactConsentAction()
                                 */
                                'action' => 'modifier-email-contact-consent',
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
                    'mission-enseignement' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/mission-enseignement',
                            'defaults' => [
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'ajouter' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/ajouter/:doctorant',
                                    'constraints' => [
                                        'doctorant' => '\d+',
                                    ],
                                    'defaults' => [
                                        /** @see MissionEnseignementController::ajouterAction() */
                                        'controller' => MissionEnseignementController::class,
                                        'action' => 'ajouter',
                                    ],
                                ],
                            ],
                            'retirer' => [
                                'type' => Segment::class,
                                'options' => [
                                    'route' => '/retirer/:mission',
                                    'defaults' => [
                                        /** @see MissionEnseignementController::retirerAction() */
                                        'controller' => MissionEnseignementController::class,
                                        'action' => 'retirer',
                                    ],
                                ],
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

                ],
            ],
        ],
    ],
    'form_elements' => [
        'factories' => [
            MailConsentementForm::class => MailConsentementFormFactory::class,
            MissionEnseignementForm::class => MissionEnseignementFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            MissionEnseignementHydrator::class => MissionEnseignementHydratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            'DoctorantService' => DoctorantServiceFactory::class,
            DoctorantSearchService::class => DoctorantSearchServiceFactory::class,
            DoctorantAssertion::class => DoctorantAssertionFactory::class,
            MissionEnseignementService::class => MissionEnseignementServiceFactory::class,
            TheseAssertion::class => TheseAssertionFactory::class,
            TheseEntityAssertion::class => TheseEntityAssertionFactory::class,
        ],
        'aliases' => [
            DoctorantService::class => 'DoctorantService',
        ]
    ],
    'controllers' => [
        'invokables' => [],
        'factories' => [
            DoctorantController::class => DoctorantControllerFactory::class,
            MissionEnseignementController::class => MissionEnseignementControllerFactory::class,
        ],
        'aliases' => [
            'Application\Controller\Doctorant' => DoctorantController::class,
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
