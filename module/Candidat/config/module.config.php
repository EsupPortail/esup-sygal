<?php

namespace Candidat;

use Candidat\Controller\CandidatController;
use Candidat\Controller\CandidatControllerFactory;
use Candidat\Provider\Privilege\CandidatPrivileges;
use Candidat\Service\CandidatService;
use Candidat\Service\CandidatServiceFactory;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydrator;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydratorFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use HDR\Assertion\HDRAssertion;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Candidat\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Candidat/Entity/Db/Mapping',
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
                            CandidatPrivileges::CANDIDAT_AFFICHER_EMAIL_CONTACT,
                            CandidatPrivileges::CANDIDAT_MODIFIER_EMAIL_CONTACT,
                        ],
                        'resources' => ['HDR'],
                        'assertion' => HDRAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => CandidatController::class,
                    'action' => [
                        'modifier-email-contact',
                        'modifier-email-contact-consent',
                    ],
                    'privileges' => CandidatPrivileges::CANDIDAT_MODIFIER_EMAIL_CONTACT,
                    'assertion'  => HDRAssertion::class,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'recherche-candidat' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/recherche-candidat',
                    'defaults' => [
                        'controller' => CandidatController::class,
                        'action' => 'rechercher',
                    ],
                ],
                'may_terminate' => true,
            ],
            'candidat' => [
                'type' => LIteral::class,
                'options' => [
                    'route' => '/candidat',
                    'defaults' => [
                        'controller' => CandidatController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'voir' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/voir/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/restaurer/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
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
                                'controller' => CandidatController::class,
                                'action' => 'rechercher',
                            ],
                        ],
                    ],
                    'modifier-email-contact' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-email-contact/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
                            ],
                            'defaults' => [
                                /**
                                 * @see CandidatController::modifierEmailContactAction()
                                 */
                                'action' => 'modifier-email-contact',
                            ],
                        ],
                    ],
                    'modifier-email-contact-consent' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier-email-contact-consent/:candidat',
                            'constraints' => [
                                'candidat' => '\d+',
                            ],
                            'defaults' => [
                                /**
                                 * @see CandidatController::modifierEmailContactConsentAction()
                                 */
                                'action' => 'modifier-email-contact-consent',
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
            
        ],
    ],
    'hydrators' => [
        'factories' => [
            MissionEnseignementHydrator::class => MissionEnseignementHydratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            CandidatService::class => CandidatServiceFactory::class,
            CandidatSearchService::class => CandidatSearchServiceFactory::class,
            CandidatAssertion::class => CandidatAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [],
        'factories' => [
            CandidatController::class => CandidatControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'urlCandidat' => 'Candidat\Controller\Plugin\UrlCandidat',
        ],
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
];
