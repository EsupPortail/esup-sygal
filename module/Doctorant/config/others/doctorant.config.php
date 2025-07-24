<?php

namespace Doctorant;

use Application\Navigation\ApplicationNavigationFactory;
use Doctorant\Assertion\DoctorantAssertion;
use Doctorant\Assertion\DoctorantAssertionFactory;
use Doctorant\Assertion\These\TheseAssertion;
use Doctorant\Assertion\These\TheseAssertionFactory;
use Doctorant\Assertion\These\TheseEntityAssertion;
use Doctorant\Assertion\These\TheseEntityAssertionFactory;
use Doctorant\Controller\DoctorantController;
use Doctorant\Controller\DoctorantControllerFactory;
use Doctorant\Controller\DoctorantRechercheController;
use Doctorant\Controller\DoctorantRechercheControllerFactory;
use Doctorant\Controller\MissionEnseignementController;
use Doctorant\Controller\MissionEnseignementControllerFactory;
use Doctorant\Facade\TableauDeBordDoctorantFacade;
use Doctorant\Facade\TableauDeBordDoctorantFacadeFactory;
use Doctorant\Form\Doctorant\DoctorantForm;
use Doctorant\Form\Doctorant\DoctorantFormFactory;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Form\MailConsentementFormFactory;
use Doctorant\Form\MissionEnseignement\MissionEnseignementForm;
use Doctorant\Form\MissionEnseignement\MissionEnseignementFormFactory;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydrator;
use Doctorant\Form\MissionEnseignement\MissionEnseignementHydratorFactory;
use Doctorant\Hydrator\Doctorant\DoctorantHydrator;
use Doctorant\Hydrator\Doctorant\DoctorantHydratorFactory;
use Doctorant\Provider\Privilege\DoctorantPrivileges;
use Doctorant\Provider\Privilege\MissionEnseignementPrivileges;
use Doctorant\Service\DoctorantService;
use Doctorant\Service\DoctorantServiceFactory;
use Doctorant\Service\MissionEnseignement\MissionEnseignementService;
use Doctorant\Service\MissionEnseignement\MissionEnseignementServiceFactory;
use Doctorant\Service\Search\DoctorantSearchService;
use Doctorant\Service\Search\DoctorantSearchServiceFactory;
use Doctorant\View\Helper\MissionEnseignementViewHelper;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Doctorant' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    //
                    // sans passage par une assertion
                    [
                        'privileges' => [
                            DoctorantPrivileges::DOCTORANT_CONSULTER_TOUS,
                            DoctorantPrivileges::DOCTORANT_MODIFIER_TOUS,
                        ],
                        'resources' => ['Doctorant'],
                    ],
                    //
                    // avec passage par une assertion
//                    [
//                        'privileges' => [
//                            DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT,
//                            DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT,
//                        ],
//                        'resources' => ['These'],
//                        'assertion' => TheseAssertion::class,
//                    ],
                    [
                        'privileges' => [
                            DoctorantPrivileges::DOCTORANT_AFFICHER_EMAIL_CONTACT,
                            DoctorantPrivileges::DOCTORANT_MODIFIER_EMAIL_CONTACT,
                        ],
                        'resources' => ['Doctorant'],
                        'assertion' => DoctorantAssertion::class,
                    ],
                    [
                        'privileges' => [
                            DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN,
                            DoctorantPrivileges::DOCTORANT_MODIFIER_SIEN,
                        ],
                        'resources' => ['Doctorant'],
                        'assertion' => DoctorantAssertion::class,
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
                    'controller' => DoctorantRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                        'notres',
                        'notresFilters',
                    ],
                    'privileges' => [
                        DoctorantPrivileges::DOCTORANT_LISTER_TOUT,
                        DoctorantPrivileges::DOCTORANT_LISTER_SIEN,
                    ],
//                    'roles' => 'user', //////////////////////////////////////// todo provisoire !!!!!!!!!!!!
                ],
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'tableau-de-bord',
                    ],
                    'privileges' => [
                        DoctorantPrivileges::DOCTORANT_CONSULTER_TOUS,
                        DoctorantPrivileges::DOCTORANT_CONSULTER_SIEN,
                    ],
                    'assertion'  => DoctorantAssertion::class,
                ],
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        DoctorantPrivileges::DOCTORANT_MODIFIER_TOUS,
                        DoctorantPrivileges::DOCTORANT_MODIFIER_SIEN,
                    ],
                    'assertion'  => DoctorantAssertion::class,
                ],
                [
                    'controller' => DoctorantController::class,
                    'action' => [
                        'rechercher',
                        'lister',
                    ],
                    'privileges' => [
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
//            'recherche-doctorant' => [
//                'type' => Segment::class,
//                'options' => [
//                    'route' => '/recherche-doctorant',
//                    'defaults' => [
//                        'controller' => DoctorantController::class,
//                        'action' => 'rechercher',
//                    ],
//                ],
//                'may_terminate' => true,
//            ],
            'doctorant' => [
                'type' => LIteral::class,
                'options' => [
                    'route' => '/doctorant',
                    'defaults' => [
                        'controller' => DoctorantRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'recherche' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => DoctorantRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'notres' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/notres',
                                    'defaults' => [
                                        'action' => 'notres',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filters' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/filters',
                                            'defaults' => [
                                                'action' => 'notresFilters',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'tableau-de-bord' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/tableau-de-bord/:doctorant',
                            'constraints' => [
                                'doctorant' => '\d+',
                            ],
                            'defaults' => [
                                /** @see DoctorantController::tableauDeBordAction() */
                                'controller' => DoctorantController::class,
                                'action' => 'tableau-de-bord',
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
                                'controller' => DoctorantController::class,
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
                                'controller' => DoctorantController::class,
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
                                'controller' => DoctorantController::class,
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter',
                            'defaults' => [
                                'controller' => DoctorantController::class,
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
                                 * @see DoctorantController::modifierEmailContactAction
                                 */
                                'controller' => DoctorantController::class,
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
                                 * @see DoctorantController::modifierEmailContactConsentAction
                                 */
                                'controller' => DoctorantController::class,
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
                                 * @see DoctorantController::consentementAction
                                 */
                                'controller' => DoctorantController::class,
                                'action' => 'consentement',
                            ],
                        ],
                    ],
                    'mission-enseignement' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/mission-enseignement',
                            'defaults' => [
                                'controller' => MissionEnseignementController::class,
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
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'doctorants' => [
                                'label'    => 'Doctorant·es',
                                'route'    => 'doctorant',
                                'resource' => PrivilegeController::getResourceId(DoctorantRechercheController::class, 'index'),
                                'privilege' => [
                                    DoctorantPrivileges::DOCTORANT_LISTER_TOUT,
                                ],
                                'icon'     => 'fas fa-user-graduate',
                                'order'    => 65,
                            ],
                        ],
                    ],
                    /**
                     * Cette page aura une page fille 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_DOCTORANTS_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Nos doctorants',
                        'route' => 'doctorant/recherche/notres',
                        'resource' => PrivilegeController::getResourceId(DoctorantRechercheController::class, 'index'),
                        'privilege' => [
                            DoctorantPrivileges::DOCTORANT_LISTER_SIEN,
                            DoctorantPrivileges::DOCTORANT_LISTER_TOUT,
                        ],
                        'pages' => [],
                    ],
                ],
            ],
        ],
    ],
    'form_elements' => [
        'factories' => [
            DoctorantForm::class => DoctorantFormFactory::class,
            MailConsentementForm::class => MailConsentementFormFactory::class,
            MissionEnseignementForm::class => MissionEnseignementFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            DoctorantHydrator::class => DoctorantHydratorFactory::class,
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
            TableauDeBordDoctorantFacade::class => TableauDeBordDoctorantFacadeFactory::class,
        ],
        'aliases' => [
            DoctorantService::class => 'DoctorantService',
        ]
    ],
    'controllers' => [
        'invokables' => [],
        'factories' => [
            DoctorantController::class => DoctorantControllerFactory::class,
            DoctorantRechercheController::class => DoctorantRechercheControllerFactory::class,
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
    'view_helpers' => [
        'invokables' => [
            'missionEnseignement' => MissionEnseignementViewHelper::class,
        ],
    ],
];
