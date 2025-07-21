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
use Formation\Export\Xlsx\Formation\EnqueteDataProvider;
use Formation\Export\Xlsx\Formation\EnqueteDataProviderFactory;
use Formation\Export\Xlsx\Formation\EtablissementExplodeDataProcessor;
use Formation\Export\Xlsx\Formation\EtablissementExplodeDataProcessorFactory;
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
    'xlsx_export_manager' => [
        'exports' => [
            FormationStatsXlsxExport::class => [
                'writer_class' => ExcelDataTablesXlsxWriter::class,
                'input_file_path' => __DIR__ . '/../../resources/formation_stats_export_v2.xlsx',
                'data_tabs' => [
                    // 'Nom de l'onglet dans le .xlsx' => [
                    //     'source_type' => Type de la source de données : 'db' ou 'service'.
                    //     'source' => Nom de la vue en base de données, ou nom du service (implémentant XlsxExportTabDataProviderInterface).
                    //     'columns' => Tableau des noms des colonnes, ou noms des colonnes séparaés par une virgule/tabulation. Inutile pour une source de type 'service'.
                    // ],
                    FormationStatsXlsxExport::FORMATION_DATA_TAB => [
                        'source_type' => 'db',
                        'source' => 'v_extract_formation',
                        'columns' => 'module_libelle formation_id	formation_titre	session_id	session_intervenants	session_modalite	session_type	session_structures_valides	session_etab_orga	session_responsable	session_nb_nonclasses	session_nb_liste_princ	session_nb_liste_princ_etab    session_nb_liste_compl	seance_id	seance_annee_debut	seance_debut	seance_fin	seance_debut_annee_univ	seance_nb_h	seance_nb_presents	seance_nb_absents	seance_nb_presents_etab',
                        'data_processors' => [
                            [
                                'name' => EtablissementExplodeDataProcessor::class,
                                'columns' => ['session_nb_liste_princ_etab'], // colonnes à "exploser"
                                'preserve_columns' => true,
                            ],
                            [
                                'name' => EtablissementExplodeDataProcessor::class,
                                'columns' => ['seance_nb_presents_etab'], // colonnes à "exploser"
                                'preserve_columns' => true,
                            ],
                        ],
                        'column_value_filters' => [
                            // fait finalement en bdd (cf. function annee_univ_compute_from_date() :
                            //['name' => AnneeUnivColumnValueFilter::class, 'params' => ['column' => 'seance_debut_annee_univ']],
                        ],
                    ],
//                    FormationStatsXlsxExport::ENQUETE_DATA_TAB => [
//                        'source_type' => 'db',
//                        'source' => 'v_extract_formation_enquete',
//                        'columns' => 'formation_id	formation_libelle	session_id	question_categorie	question_libelle	reponse_niveau	reponse_commentaire	reponse_date',
//                    ],
                    FormationStatsXlsxExport::ENQUETE_DATA_TAB => [
                        'source_type' => 'service',
                        'source' => EnqueteDataProvider::class,
                    ],
                    FormationStatsXlsxExport::DOCTORANT_DATA_TAB => [
                        'source_type' => 'db',
                        'source' => 'v_extract_formation_presence',
                        'columns' => 'formation_id,formation_libelle,session_id,session_intervenant,session_modalite,session_type,session_etab_orga,doctorant_id,doctorant_nom_usuel,doctorant_nom_patronymique,doctorant_prenoms,doctorant_etablissement,these_ed,these_ur,these_annee_doctorat,these_annees_inscription,session_date_inscription,session_statut_inscription,seance_id,seance_debut,seance_debut_annee_univ,seance_statut_presence,session_nb_heures,session_nb_heures_suivies_par_doctorant',
                        // Filtres à appliquer pour transformer des valeurs de colonnes/attributs issues de la source
                        // en nouvelles valeurs de colonnes destination.
                        // Tableau ordonné de services ou de classes implémentant obligatoirement l'interface
                        // {@see \UnicaenDbImport\Filter\ColumnValue\ColumnValueFilterInterface}.
                        'column_value_filters' => [
                            // fait finalement en bdd (cf. function annee_univ_compute_from_date() :
                             //['name' => AnneeUnivColumnValueFilter::class, 'params' => ['column' => 'seance_debut_annee_univ']],
                        ],
                    ],
                ],
            ],
        ],
        'factories' => [
            FormationStatsXlsxExport::class => FormationStatsXlsxExportFactory::class,
        ],
        'aliases' => [
            FormationStatsXlsxExport::CODE => FormationStatsXlsxExport::class,
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

    'search_services' => [
        'factories' => [
            FormationSearchService::class => FormationSearchServiceFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            FormationService::class => FormationServiceFactory::class,
            XlsxExportManager::class => XlsxExportManagerFactory::class,
            FormationAssertion::class => FormationAssertionFactory::class,

            FormationStatsXlsxExport::class => FormationStatsXlsxExportFactory::class,
            EnqueteDataProvider::class => EnqueteDataProviderFactory::class,
            EtablissementExplodeDataProcessor::class => EtablissementExplodeDataProcessorFactory::class,
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