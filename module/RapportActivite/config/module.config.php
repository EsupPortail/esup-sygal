<?php

namespace RapportActivite;

use Application\Form\Factory\RapportAvisFormFactory;
use Application\Form\Rapport\RapportAvisForm;
use Application\Navigation\ApplicationNavigationFactory;
use Application\Search\Controller\SearchControllerPluginFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use RapportActivite\Assertion\RapportActiviteAssertion;
use RapportActivite\Assertion\RapportActiviteAssertionFactory;
use RapportActivite\Assertion\Avis\RapportActiviteAvisAssertion;
use RapportActivite\Assertion\Avis\RapportActiviteAvisAssertionFactory;
use RapportActivite\Assertion\Validation\RapportActiviteValidationAssertion;
use RapportActivite\Assertion\Validation\RapportActiviteValidationAssertionFactory;
use RapportActivite\Controller\Avis\RapportActiviteAvisController;
use RapportActivite\Controller\Avis\RapportActiviteAvisControllerFactory;
use RapportActivite\Controller\RapportActiviteController;
use RapportActivite\Controller\RapportActiviteControllerFactory;
use RapportActivite\Controller\Recherche\RapportActiviteRechercheController;
use RapportActivite\Controller\Recherche\RapportActiviteRechercheControllerFactory;
use RapportActivite\Controller\Validation\RapportActiviteValidationController;
use RapportActivite\Controller\Validation\RapportActiviteValidationControllerFactory;
use RapportActivite\Event\Avis\RapportActiviteAvisEventListener;
use RapportActivite\Event\Avis\RapportActiviteAvisEventListenerFactory;
use RapportActivite\Event\Validation\RapportActiviteValidationEventListener;
use RapportActivite\Event\Validation\RapportActiviteValidationEventListenerFactory;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterFactory;
use RapportActivite\Service\Fichier\RapportActiviteFichierService;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceFactory;
use RapportActivite\Form\RapportActiviteForm;
use RapportActivite\Form\RapportActiviteFormFactory;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRule;
use RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRuleFactory;
use RapportActivite\Rule\Validation\RapportActiviteValidationRule;
use RapportActivite\Rule\Validation\RapportActiviteValidationRuleFactory;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceFactory;
use RapportActivite\Service\Search\RapportActiviteSearchService;
use RapportActivite\Service\Search\RapportActiviteSearchServiceFactory;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\RapportActiviteServiceFactory;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [

    // Options concernant les rapports d'activité
    'rapport-activite' => [
        // Page de couverture des rapports d'activité déposés
        'page_de_couverture' => [
            'template' => [
                // template .phtml
                'phtml_file_path' => __DIR__ . '/../view/rapport-activite/page-de-validation/template.phtml',
                // feuille de styles
                'css_file_path' => __DIR__ . '/../view/rapport-activite/page-de-validation/styles.css',
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'RapportActivite\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'paths' => [
                    __DIR__ . '/../src/RapportActivite/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'RapportActivite' => [],
                'RapportActiviteAvis' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            // Dépôt, visualisation, etc.
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                        ],
                        'resources' => ['RapportActivite'],
                        'assertion' => RapportActiviteAssertion::class,
                    ],
                    [
                        'privileges' => [
                            // Avis
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN,
                        ],
                        'resources' => ['RapportActiviteAvis'],
                        'assertion' => RapportActiviteAvisAssertion::class,
                    ],
                    [
                        'privileges' => [
                            // Validation
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN,
                        ],
                        'resources' => ['RapportActivite'],
                        'assertion' => RapportActiviteValidationAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                //
                // Dépôt, visualisation, etc.
                //
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'consulter',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'telecharger',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                    ],
                    'assertion' => Assertion\RapportActiviteAssertion::class,
                ],

                //
                // Recherche
                //
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action' => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_ZIP,
                    'assertion' => Assertion\RapportActiviteAssertion::class,
                ],

                //
                // Avis
                //
                [
                    'controller' => RapportActiviteAvisController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN,
                    ],
                    'assertion' => RapportActiviteAvisAssertion::class,
                ],
                [
                    'controller' => RapportActiviteAvisController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_AVIS_SIEN,
                    ],
                    'assertion' => RapportActiviteAvisAssertion::class,
                ],
                [
                    'controller' => RapportActiviteAvisController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN,
                    ],
                    'assertion' => RapportActiviteAvisAssertion::class,
                ],

                //
                // Validation
                //
                [
                    'controller' => RapportActiviteValidationController::class,
                    'action' => [
                        'valider',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteValidationController::class,
                    'action' => [
                        'devalider',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'rapport-activite' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/rapport-activite',
                    'defaults' => [
                        'controller' => RapportActiviteController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'recherche' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => RapportActiviteRechercheController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'index' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/index',
                                    'defaults' => [
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'telecharger-zip' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/telecharger-zip',
                                    'defaults' => [
                                        'action' => 'telecharger-zip',
                                        /* @see \RapportActivite\Controller\Recherche\RapportActiviteRechercheController::telechargerZipAction() */
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'consulter' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/consulter/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                                /* @see \RapportActivite\Controller\RapportActiviteController::consulterAction() */
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ajouter/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'ajouter',
                                /* @see \RapportActivite\Controller\RapportActiviteController::ajouterAction() */
                            ],
                        ],
                    ],
                    'telecharger' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/telecharger/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'telecharger',
                                /* @see \RapportActivite\Controller\RapportActiviteController::telechargerAction() */
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/supprimer/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'supprimer',
                                /* @see RapportActiviteController::supprimerAction() */
                            ],
                        ],
                    ],
                    'valider' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/valider/:rapport/type/:typeValidation',
                            'constraints' => [
                                'rapport' => '\d+',
                                'typeValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => RapportActiviteValidationController::class,
                                'action' => 'valider',
                                /* @see \RapportActivite\Controller\Validation\RapportActiviteValidationController::validerAction() */
                            ],
                        ],
                    ],
                    'devalider' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/devalider/:rapportValidation',
                            'constraints' => [
                                'rapportValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => RapportActiviteValidationController::class,
                                'action' => 'devalider',
                                /* @see RapportActiviteValidationController::devaliderAction() */
                            ],
                        ],
                    ],
                    'avis' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/avis',
                            'defaults' => [
                                'controller' => RapportActiviteAvisController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'ajouter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/ajouter/:rapport',
                                    'constraints' => [
                                        'rapport' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'ajouter',
                                        /* @see \RapportActivite\Controller\Avis\RapportActiviteAvisController::ajouterAction() */
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/modifier/:rapportAvis',
                                    'constraints' => [
                                        'rapportAvis' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'modifier',
                                        /* @see \RapportActivite\Controller\Avis\RapportActiviteAvisController::modifierAction() */
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/supprimer/:rapportAvis',
                                    'constraints' => [
                                        'rapportAvis' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'supprimer',
                                        /* @see \RapportActivite\Controller\Avis\RapportActiviteAvisController::supprimerAction() */
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
                    /**
                     * Navigation pour LA thèse "sélectionnée".
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => $thesePages = [
                            // DEPTH = 3
                            'rapport-activite' => [
                                'id' => 'these-rapport-activite',
                                'label' => "Rapports d'activité",
                                'order' => 20 ,
                                'route' => 'rapport-activite/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportActiviteController::class, 'consulter'),
                                'visible' => Assertion\RapportActiviteAssertion::class,
                            ],
                        ],
                    ],

                    /**
                     * Page pour Doctorant.
                     * Cette page sera dupliquée en 'ma-these-1', 'ma-these-2', etc. automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MA_THESE_PAGE_ID => [
                        'pages' => $thesePages,
                    ],

                    /**
                     * Cette page aura une page fille 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_THESES_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'RAPPORTS_ACTIVITES' => [
                                'label' => '(Rapports activité Structure)',
                                'route' => 'rapport-activite/recherche/index',
                                'resource' => PrivilegeController::getResourceId(RapportActiviteRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                                ],
                                'visible' => Assertion\RapportActiviteAssertion::class,
                            ],
                        ],
                    ],

                    'admin' => [
                        'pages' => [
                            '-----------' => [
                                'label' => null,
                                'order' => 99,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'rapport-activite' => [
                                'label' => "Rapports d'activité",
                                'route' => 'rapport-activite/recherche/index',
                                'order' => 100,
                                'resource' => PrivilegeController::getResourceId(RapportActiviteRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                                ],
                                'visible' => Assertion\RapportActiviteAssertion::class,
                            ],
                            '----------' => [
                                'label' => null,
                                'order' => 103,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RapportActiviteService::class => RapportActiviteServiceFactory::class,
            RapportActiviteSearchService::class => RapportActiviteSearchServiceFactory::class,
            RapportActiviteValidationService::class => RapportActiviteValidationServiceFactory::class,
            RapportActiviteAvisService::class => RapportActiviteAvisServiceFactory::class,

            RapportActiviteAssertion::class => RapportActiviteAssertionFactory::class,
            RapportActiviteAvisAssertion::class => RapportActiviteAvisAssertionFactory::class,
            RapportActiviteValidationAssertion::class => RapportActiviteValidationAssertionFactory::class,

            RapportActiviteFichierService::class => RapportActiviteFichierServiceFactory::class,

            RapportActiviteAvisEventListener::class => RapportActiviteAvisEventListenerFactory::class,
            RapportActiviteValidationEventListener::class => RapportActiviteValidationEventListenerFactory::class,

            RapportActiviteAvisNotificationRule::class => RapportActiviteAvisNotificationRuleFactory::class,
            RapportActiviteValidationRule::class => RapportActiviteValidationRuleFactory::class,

            PageValidationPdfExporter::class => PageValidationPdfExporterFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RapportActiviteController::class => RapportActiviteControllerFactory::class,
            RapportActiviteRechercheController::class => RapportActiviteRechercheControllerFactory::class,
            RapportActiviteValidationController::class => RapportActiviteValidationControllerFactory::class,
            RapportActiviteAvisController::class => RapportActiviteAvisControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'searchControllerPlugin' => SearchControllerPluginFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            RapportActiviteForm::class => RapportActiviteFormFactory::class,
            RapportAvisForm::class => RapportAvisFormFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];