<?php

namespace RapportActivite;

use Application\Form\Factory\RapportAvisFormFactory;
use Application\Form\Rapport\RapportAvisForm;
use Application\Navigation\ApplicationNavigationFactory;
use Application\Search\Controller\SearchControllerPluginFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use RapportActivite\Assertion\Avis\RapportActiviteAvisAssertion;
use RapportActivite\Assertion\Avis\RapportActiviteAvisAssertionFactory;
use RapportActivite\Assertion\RapportActiviteAssertion;
use RapportActivite\Assertion\RapportActiviteAssertionFactory;
use RapportActivite\Assertion\Recherche\RapportActiviteRechercheAssertion;
use RapportActivite\Assertion\Recherche\RapportActiviteRechercheAssertionFactory;
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
use RapportActivite\Event\RapportActiviteEventListener;
use RapportActivite\Event\RapportActiviteEventListenerFactory;
use RapportActivite\Event\Validation\RapportActiviteValidationEventListener;
use RapportActivite\Event\Validation\RapportActiviteValidationEventListenerFactory;
use RapportActivite\Form\RapportActiviteAnnuelForm;
use RapportActivite\Form\RapportActiviteAnnuelFormFactory;
use RapportActivite\Form\RapportActiviteFinContratForm;
use RapportActivite\Form\RapportActiviteFinContratFormFactory;
use RapportActivite\Provider\Privilege\RapportActivitePrivileges;
use RapportActivite\Rule\Avis\RapportActiviteAvisRule;
use RapportActivite\Rule\Avis\RapportActiviteAvisRuleFactory;
use RapportActivite\Rule\Creation\RapportActiviteCreationRule;
use RapportActivite\Rule\Creation\RapportActiviteCreationRuleFactory;
use RapportActivite\Rule\Operation\Notification\OperationAttendueNotificationRule;
use RapportActivite\Rule\Operation\Notification\OperationAttendueNotificationRuleFactory;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleFactory;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceFactory;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporterFactory;
use RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporter;
use RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporterFactory;
use RapportActivite\Service\Fichier\RapportActiviteFichierService;
use RapportActivite\Service\Fichier\RapportActiviteFichierServiceFactory;
use RapportActivite\Service\Notification\RapportActiviteNotificationFactory;
use RapportActivite\Service\Notification\RapportActiviteNotificationFactoryFactory;
use RapportActivite\Service\Operation\RapportActiviteOperationService;
use RapportActivite\Service\Operation\RapportActiviteOperationServiceFactory;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\RapportActiviteServiceFactory;
use RapportActivite\Service\Search\RapportActiviteSearchService;
use RapportActivite\Service\Search\RapportActiviteSearchServiceFactory;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

const VALIDATION_DOCTORANT = 'VALIDATION_DOCTORANT';
const AVIS_GEST = 'AVIS_GEST';
const AVIS_DIR_THESE = 'AVIS_DIR_THESE';
const AVIS_CODIR_THESE = 'AVIS_CODIR_THESE';
const AVIS_DIR_UR = 'AVIS_DIR_UR';
const AVIS_DIR_ED = 'AVIS_DIR_ED';
const VALIDATION_AUTO = 'VALIDATION_AUTO';

return [
    // Options concernant les rapports d'activité
    'rapport-activite' => [
        // Date butoire pour le rendu du rapport d'activité : jj/mm
        'date_butoire_?????' => '15/06',

        'template' => [
            // templates .phtml
            'template_path' => __DIR__ . '/../view/rapport-activite/rapport-activite/pdf/template.phtml',
            'footer_path' => 'footer.phtml',
            // feuille de styles
            'css_path' => [
                __DIR__ . '/../view/rapport-activite/pdf/common-styles.css',
                __DIR__ . '/../view/rapport-activite/rapport-activite/pdf/styles.css',
            ],
        ],

        // Page de validation des rapports d'activité déposés
        'page_de_validation' => [
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
                'RapportActiviteValidation' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            // Dépôt, visualisation, etc.
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_MODIFIER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_TOUT,
                            RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_SIEN,
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
                        'resources' => ['RapportActiviteValidation'],
                        'assertion' => RapportActiviteValidationAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                //
                // Visualisation, création, etc.
                //
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'lister',
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
                        'consulter',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_CONSULTER_SIEN,
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
                        'generer',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_GENERER_SIEN,
                    ],
                    'assertion' => RapportActiviteAssertion::class,
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action' => [
                        'ajouter',
                        'modifier',
                    ],
                    'privileges' => [
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_TOUT,
                        RapportActivitePrivileges::RAPPORT_ACTIVITE_AJOUTER_SIEN,
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
                    'assertion' => RapportActiviteAssertion::class,
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
//                    'privileges' => [
//                        RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
//                        RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
//                    ],
                    'assertion' => RapportActiviteRechercheAssertion::class,
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action' => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportActivitePrivileges::RAPPORT_ACTIVITE_TELECHARGER_ZIP,
                    'assertion' => RapportActiviteRechercheAssertion::class,
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
                    'assertion' => RapportActiviteValidationAssertion::class,
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
                    'assertion' => RapportActiviteValidationAssertion::class,
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
                    'lister' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/lister/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'lister',
                                /* @see \RapportActivite\Controller\RapportActiviteController::listerAction() */
                            ],
                        ],
                    ],
                    'consulter' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/consulter/:these/:rapport',
                            'constraints' => [
                                'these' => '\d+',
                                'rapport' => '\d+',
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
                            'route' => '/ajouter/:these/:estFinContrat',
                            'constraints' => [
                                'these' => '\d+',
                                'estFinContrat' => '[0-1]',
                            ],
                            'defaults' => [
                                'action' => 'ajouter',
                                /* @see \RapportActivite\Controller\RapportActiviteController::ajouterAction() */
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/modifier/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'modifier',
                                /* @see RapportActiviteController::modifierAction() */
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
                    'generer' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/generer/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'generer',
                                /* @see \RapportActivite\Controller\RapportActiviteController::genererAction() */
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
                                    'route' => '/ajouter/:rapport/type/:typeAvis',
                                    'constraints' => [
                                        'rapport' => '\d+',
                                        'typeAvis' => '\d+',
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
                                'route' => 'rapport-activite/lister',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportActiviteController::class, 'lister'),
                                'visible' => RapportActiviteAssertion::class,

                                'pages' => [
                                    'consulter' => [ // juste pour le Fil d'Ariane
                                        'label' => "Détails",
                                        'route' => 'rapport-activite/consulter',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'rapport',
                                        ],
                                        'visible' => false
                                    ],
                                    'ajouter' => [
                                        'label' => "Nouveau rapport",
                                        'route' => 'rapport-activite/ajouter',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                        'visible' => false,
                                    ],
                                ],
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
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                                ],
                                'visible' => RapportActiviteRechercheAssertion::class,
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
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                                    RapportActivitePrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                                ],
                                'visible' => RapportActiviteRechercheAssertion::class,
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
            RapportActiviteOperationService::class => RapportActiviteOperationServiceFactory::class,

            RapportActiviteNotificationFactory::class => RapportActiviteNotificationFactoryFactory::class,

            RapportActiviteAssertion::class => RapportActiviteAssertionFactory::class,
            RapportActiviteRechercheAssertion::class => RapportActiviteRechercheAssertionFactory::class,
            RapportActiviteAvisAssertion::class => RapportActiviteAvisAssertionFactory::class,
            RapportActiviteValidationAssertion::class => RapportActiviteValidationAssertionFactory::class,

            RapportActiviteFichierService::class => RapportActiviteFichierServiceFactory::class,

            RapportActiviteEventListener::class => RapportActiviteEventListenerFactory::class,
            RapportActiviteAvisEventListener::class => RapportActiviteAvisEventListenerFactory::class,
            RapportActiviteValidationEventListener::class => RapportActiviteValidationEventListenerFactory::class,

            RapportActiviteAvisRule::class => RapportActiviteAvisRuleFactory::class,
            OperationAttendueNotificationRule::class => OperationAttendueNotificationRuleFactory::class,
            RapportActiviteCreationRule::class => RapportActiviteCreationRuleFactory::class,
            RapportActiviteOperationRule::class => RapportActiviteOperationRuleFactory::class,

            PageValidationPdfExporter::class => PageValidationPdfExporterFactory::class,
            RapportActivitePdfExporter::class => RapportActivitePdfExporterFactory::class,
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
            RapportActiviteAnnuelForm::class => RapportActiviteAnnuelFormFactory::class,
            RapportActiviteFinContratForm::class => RapportActiviteFinContratFormFactory::class,
            RapportAvisForm::class => RapportAvisFormFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];