<?php

namespace Formation;

use Application\Export\Xlsx\Manager\XlsxExportManager;
use Application\Export\Xlsx\Manager\XlsxExportManagerFactory;
use Application\Export\Xlsx\Writer\ExcelDataTables\ExcelDataTablesXlsxWriter;
use Application\Navigation\ApplicationNavigationFactory;
use Formation\Assertion\Formation\FormationAssertion;
use Formation\Assertion\Formation\FormationAssertionFactory;
use Formation\Controller\FormationController;
use Formation\Controller\FormationControllerFactory;
use Formation\Controller\Recherche\FormationRechercheController;
use Formation\Controller\Recherche\FormationRechercheControllerFactory;
use Formation\Export\Xlsx\Formation\FormationStatsXlsxExport;
use Formation\Export\Xlsx\Formation\FormationStatsXlsxExportFactory;
use Formation\Form\Formation\FormationForm;
use Formation\Form\Formation\FormationFormFactory;
use Formation\Form\Formation\FormationHydrator;
use Formation\Form\Formation\FormationHydratorFactory;
use Formation\Provider\Privilege\FormationPrivileges;
use Formation\Service\Formation\FormationService;
use Formation\Service\Formation\FormationServiceFactory;
use Formation\Service\Formation\Search\FormationSearchService;
use Formation\Service\Formation\Search\FormationSearchServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'xlsx_export' => [
        'factories' => [
            FormationStatsXlsxExport::class => FormationStatsXlsxExportFactory::class,
        ],
        'aliases' => [
            FormationStatsXlsxExport::CODE => FormationStatsXlsxExport::class,
        ],
        'config' => [
            FormationStatsXlsxExport::class => [
                'writer_class' => ExcelDataTablesXlsxWriter::class,
                'input_file_path' => __DIR__ . '/../../resources/formation_stats_export.xlsx',
                'data_tabs' => [
                    FormationStatsXlsxExport::FORMATION_DATA_TAB => [
                        'view_name' => 'v_formation_export',
                        'view_columns' => 'formation_id	formation_titre	session_id	session_intervenants	session_modalite	session_type	session_structures_valides	session_etab_orga	session_responsable	session_nb_nonclasses	session_nb_liste_princ	session_nb_liste_compl	seance_id	seance_annee_debut	seance_debut	seance_fin	seance_nb_h	seance_nb_presents	seance_nb_absents	seance_nb_presents_etab',
                    ],
                    FormationStatsXlsxExport::ENQUETE_DATA_TAB => [
                        'view_name' => 'v_formation_enquete_export',
                        'view_columns' => 'formation_id	formation_libelle	session_id	question_categorie	question_libelle	reponse_niveau	reponse_commentaire	reponse_date',
                    ],
                ],
            ],
        ],
    ],

    'xlsx_exports' => [
        FormationStatsXlsxExport::class => [
            'factory' => FormationStatsXlsxExportFactory::class,
            'alias' => FormationStatsXlsxExport::CODE,
            'writer_class' => ExcelDataTablesXlsxWriter::class,
            'input_file_path' => __DIR__ . '/../../resources/formation_stats_export.xlsx',
            'data_tabs' => [
                FormationStatsXlsxExport::FORMATION_DATA_TAB => [
                    'view_name' => 'v_formation_export',
                    'view_columns' => 'formation_id	formation_titre	session_id	session_intervenants	session_modalite	session_type	session_structures_valides	session_etab_orga	session_responsable	session_nb_nonclasses	session_nb_liste_princ	session_nb_liste_compl	seance_id	seance_annee_debut	seance_debut	seance_fin	seance_nb_h	seance_nb_presents	seance_nb_absents	seance_nb_presents_etab',
                ],
                FormationStatsXlsxExport::ENQUETE_DATA_TAB => [
                    'view_name' => 'v_formation_enquete_export',
                    'view_columns' => 'formation_id	formation_libelle	session_id	question_categorie	question_libelle	reponse_niveau	reponse_commentaire	reponse_date',
                ],
            ],
        ],
    ],

    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            FormationPrivileges::FORMATION_MODIFIER,
                        ],
//                        'resources' => ['Fichier'],
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FormationRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_INDEX,
                    ],
                ],
                [
                    'controller' => FormationRechercheController::class,
                    'action' => [
                        'exporter-xls',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_EXPORTER_XLS,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'afficher',
                        'telecharger-fiche',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_AFFICHER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_AJOUTER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'modifier',
                        'supprimer-fiche'
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_MODIFIER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_HISTORISER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_SUPPRIMER,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    ApplicationNavigationFactory::FORMATIONS_PAGE_ID => [
                        'pages' => [
                            ApplicationNavigationFactory::FORMATIONS_PAGE_ID => [
                                'label'    => 'Formations',
                                'route'    => 'formation/formation',
                                'order'    => 110,
                                'pages' => [
                                    'afficher' => [
                                        'label'    => "Affichage d'une formation",
                                        'route'    => 'formation/formation/afficher',
                                        'resource' => PrivilegeController::getResourceId(FormationController::class, 'afficher') ,
                                        'order'    => 100,
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'formation' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/formation',
                            'defaults' => [
                                'controller' => FormationRechercheController::class,
                                'action'     => 'index',
                            ],
                        ],
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
                            'exporter-xls' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/exporter/xls/:code[/formation/:formation[/session/:session]]',
                                    'defaults' => [
                                        /** @see FormationRechercheController::exporterXlsAction() */
                                        'action' => 'exporter-xls',
                                    ],
                                ],
                            ],
                            'afficher' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/afficher/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'afficher',
                                    ],
                                ],
                            ],
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter[/:module]',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                            'supprimer-fiche' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer-fiche/:formation/:fichier',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        /** @see FormationController::supprimerFicheAction */
                                        'action'     => 'supprimer-fiche',
                                    ],
                                ],
                            ],
                            'telecharger-fiche' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/telecharger-fiche/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        /** @see FormationController::telechargerFicheAction() */
                                        'action'     => 'telecharger-fiche',
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
        'factories' => [
            FormationService::class => FormationServiceFactory::class,
            FormationSearchService::class => FormationSearchServiceFactory::class,
            XlsxExportManager::class => XlsxExportManagerFactory::class,
            FormationAssertion::class => FormationAssertionFactory::class,
            FormationStatsXlsxExport::class => FormationStatsXlsxExportFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            FormationController::class => FormationControllerFactory::class,
            FormationRechercheController::class => FormationRechercheControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            FormationForm::class => FormationFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            FormationHydrator::class => FormationHydratorFactory::class,
        ],
    ]

];