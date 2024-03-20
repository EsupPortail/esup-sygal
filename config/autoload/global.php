<?php

namespace Application;

use Application\Entity\Db\Source;
use Application\Navigation\NavigationFactoryFactory;
use Import\Filter\PrefixEtabColumnValueFilter;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Retraitement\Filter\Command\RetraitementShellCommandMines;

$config = [
    'api-tools-content-negotiation' => [
        'selectors' => [],
    ],
    'db' => [
        'adapters' => [
            'dummy' => [],
        ],
    ],
    'api-tools-mvc-auth' => [
        'authentication' => [
            'map' => [
                'Api\\V1' => 'basic',
            ],
        ],
    ],
    'translator' => [
        'locale' => 'fr_FR',
    ],
    'sygal' => [
        // Préfixe par défaut à utiliser pour générer le SOURCE_CODE d'un enregistrement créé dans l'application
        'default_prefix_for_source_code' => 'SyGAL',
        // Page de couverture
        'page_de_couverture' => [
            'template' => [
                // template .phtml
                'phtml_file_path' => APPLICATION_DIR . '/module/Depot/view/depot/depot/page-de-couverture/pagedecouverture.phtml',
                // feuille de styles
                'css_file_path' => APPLICATION_DIR . '/module/Depot/view/depot/depot/page-de-couverture/pagedecouverture.css',
            ],
        ],
        // Options pour le test d'archivabilité
        'archivabilite' => [
            'check_ws_script_path' => __DIR__ . '/../../bin/from_cines/check_webservice_response.sh',
            'script_path'          => __DIR__ . '/../../bin/validation_cines.sh',
            'proxy' => [
                'enabled' => false,
                //'proxy_host' => 'http://proxy.unicaen.fr',
                //'proxy_port' => 3128,
            ],
        ],
        // Options pour le retraitement des fichiers PDF
        'retraitement' => [
            // Commande utilisée pour retraiter les fichiers PDF, et ses options.
            'command' => [
                'class' => RetraitementShellCommandMines::class,
                'options' => [
                    'pdftk_path' => 'pdftk',
                    'gs_path'    => 'gs',
                    //'gs_args' => '-dPDFACompatibilityPolicy=1'
                ],
            ],
            // Durée au bout de laquelle le retraitement est interrompu pour le relancer en tâche de fond.
            // Valeurs possibles: '30s', '1m', etc. (cf. "man timeout").
            'timeout' => '20s',
        ],
        // Options concernant le dépôt de la version corrigée
        'depot_version_corrigee' => [
            // Resaisir l'autorisation de diffusion ? Sinon celle saisie au 1er dépôt est reprise/dupliquée.
            'resaisir_autorisation_diffusion' => true,
            // Resaisir les attestations ? Sinon celles saisies au 1er dépôt sont reprises/dupliquées.
            'resaisir_attestations' => true,
        ],
        // Options concernant les rapports (CSI, mi-parcours)
        'rapport' => [
            // Page de couverture des rapports d'activité déposés
            'page_de_couverture' => [
                'template' => [
                    // template .phtml
                    'phtml_file_path' => APPLICATION_DIR . '/module/Application/view/application/rapport/page-de-couverture/pagedecouverture.phtml',
                    // feuille de styles
                    'css_file_path' => APPLICATION_DIR . '/module/Application/view/application/rapport/page-de-couverture/pagedecouverture.css',
                ],
            ],
        ],
    ],
    'import' => [

        'import_observ_entity_class' => ImportObserv::class,
        'import_observ_result_entity_class' => ImportObservResult::class,

        'connections' => [
            // Cf. config locale
        ],

        //
        // Classe de l'entité Doctrine représentant une "Source".
        //
        'source_entity_class' => Source::class,

        //
        // Code de la Source par défaut (injectée dans les entités implémentant SoucreAwareInterface).
        //
        'default_source_code' => 'SYGAL::sygal',

        //
        // Alias éventuels des noms de colonnes d'historique.
        //
        'histo_columns_aliases' => [
            'created_on' => 'histo_creation',     // date/heure de création de l'enregistrement
            'updated_on' => 'histo_modification', // date/heure de modification
            'deleted_on' => 'histo_destruction',  // date/heure de suppression

            'created_by' => 'histo_createur_id',     // auteur de la création de l'enregistrement
            'updated_by' => 'histo_modificateur_id', // auteur de la modification
            'deleted_by' => 'histo_destructeur_id',  // auteur de la suppression
        ],

        //
        // Forçage éventuel de valeur pour les colonnes d'historique.
        //
        'histo_columns_values' => [
            'created_by' => 1, // auteur de la création de l'enregistrement
            'updated_by' => 1, // auteur de la modification
            'deleted_by' => 1, // auteur de la suppression
        ],

        //
        // Témoin indiquant si la table IMPORT_OBSERV doit être prise en compte.
        // La table IMPORT_OBSERV permet d'inscrire les changements de valeurs à détecter pendant la synchro.
        // Les changements détectés sont consignés dans la table IMPORT_OBSERV_RESULT.
        //
        'use_import_observ' => true,

        //
        // Imports.
        //
        'imports' => [
            [
                'name' => 'domaine-hal',
                'order' => 160,
                'source' => [
                    'name' => 'HAL',
                    'connection' => 'sygal-import-ws-hal',
                    'select' => '/domain/?q=*:*&wt=json&fl=*&rows=500',
                    'code' => 'HAL',
                    'columns' => [
                        'docid',
                        'code_s',
                        'haveNext_bool',
                        'en_domain_s',
                        'fr_domain_s',
                        'level_i',
                        'parent_i',
                    ],
                    'column_name_filter' => [
                        'docid' => 'docid',
                        'haveNext_bool' => 'havenext_bool',
                        'code_s' => 'source_code',
                        'en_domain_s' => 'en_domain_s',
                        'fr_domain_s' => 'fr_domain_s',
                        'level_i' => 'level_i',
                        'parent_i' => 'parent_id'
                    ],
                    'source_code_column' => 'source_code',
                ],
                'destination' => [
                    'name' => 'application',
                    'table' => 'tmp_domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                    'id_strategy' => null,
                    'id_sequence' => null,
                ],
            ]
            // <==== la config des imports sera injectée ici
        ],

        //
        // Synchros.
        //
        'synchros' => [
            // <==== la config des synchros sera injectée ici
            ////////////////////////////////////////////// DOMAINES HAL //////////////////////////////////////////////
            [
                'name' => 'domaine-hal',
                'order' => 160,
                'source' => [
                    'name' => 'sygal',
                    'code' => 'app',
                    'table' => 'src_domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                ],
                'destination' => [
                    'name' => 'application',
                    'table' => 'domaine_hal',
                    'connection' => 'default',
                    'source_code_column' => 'source_code',
                    'id_strategy' => 'SEQUENCE',
                    'id_sequence' => null,
                    'where' => NULL,
                ],
            ],
        ],
    ],
    // Options pour le service de notification par mail
    'notification' => [
        // préfixe à ajouter systématiquement devant le sujet des mails
        'subject_prefix' => '[ESUP-SyGAL]',
        // destinataires à ajouter systématiquement en copie simple ou cachée de tous les mails
        //'cc' => [],
        //'bcc' => [],
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache'   => 'filesystem',
                'query_cache'      => 'filesystem',
                'result_cache'     => 'filesystem',
                'hydration_cache'  => 'filesystem',
                'generate_proxies' => false,
            ],
        ],
    ],
    'languages' => [
        'language-list' => ['fr_FR', 'en_US', 'de_DE', 'it_IT'],
    ],
    'service_manager' => [
        'factories' => [
            'navigation' => NavigationFactoryFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => false,
        'display_exceptions'       => false,
    ],
];

/**
 * Il y a une déclinaison automatique des imports par établissement (pour pouvoir lancer l'import pour un
 * établissement précis) : cf. fonction {@see \generateConfigImportsForEtabs()} plus bas, appelée dans 'secret.local.php'.
 * Ce qui suit n'est que la config "commune".
 */
const CONFIG_IMPORTS = [
    [
        'name' => 'composante-enseignement-%s',
        'order' => 10,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-octopus-composante-ens',
            'select' => '/structure-light?type=2',
            'code' => 'UCN::octopus',
            'page_size' => 0,
            'columns' => [
                'sigle',
                'libelleLong',
                'code',
            ],
            'column_name_filter' => [
                'sigle' => 'sigle',
                'libelleLong' => 'libelle_long',
                'code' => 'source_code',
            ],
            'source_code_column' => 'source_code',
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['code']]],
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_composante_ens',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'structure-%s',
        'order' => 10,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/structure',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId']]],
            ],
            'column_name_filter' => [
                'typeStructureId' => 'type_structure_id',
                'sigle' => 'sigle',
                'libelle' => 'libelle',
                'codePays' => 'code_pays',
                'libellePays' => 'libelle_pays',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_structure',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'etablissement-%s',
        'order' => 20,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/etablissement',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','structureId']]],
            ],
            'column_name_filter' => [
                'structureId' => 'structure_id',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_etablissement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'ecole-doctorale-%s',
        'order' => 30,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/ecole-doctorale',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','structureId']]],
            ],
            'column_name_filter' => [
                'structureId' => 'structure_id',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_ecole_doct',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'unite-recherche-%s',
        'order' => 40,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/unite-recherche',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','structureId']]],
            ],
            'column_name_filter' => [
                'structureId' => 'structure_id',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_unite_rech',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
//    [
//        'name' => 'composante-enseignement-%s',
//        'order' => 50,
//        'source' => [
//            'name' => '%s',
//            'connection' => 'sygal-import-octopus-composante-ens',
//            'select' => '/structure-light?type=2',
//            'source_code_column' => 'SOURCE_CODE',
//            'code' => 'UCN::octopus',
//            'column_value_filter' => \Admission\Filter\PrefixEtabColumnValueFilter::class,
//            'page_size' => 0,
//            'columns' => [
//                'sigle',
//                'libelleLong',
//                'code',
//            ],
//            'column_name_filter' => [
//                'sigle' => 'SIGLE',
//                'libelleLong' => 'LIBELLE_LONG',
//                'code' => 'SOURCE_CODE',
//            ],
//            'extra' => [
//                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
//            ],
//        ],
//        'destination' => [
//            'name' => 'Application',
//            'table' => 'tmp_composante_ens',
//            'connection' => 'default',
//            'source_code_column' => 'SOURCE_CODE',
//            'id_strategy' => null,
//            'id_sequence' => null,
//        ],
//    ],
    [
        'name' => 'composante-enseignement-%s',
        'order' => 50,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-octopus-composante-ens',
            'select' => '/structure-light?type=2',
            'code' => 'UCN::octopus',
            'page_size' => 0,
            'columns' => [
                'sigle',
                'libelleLong',
                'code',
            ],
            'column_name_filter' => [
                'sigle' => 'sigle',
                'libelleLong' => 'libelle_long',
                'code' => 'source_code',
            ],
            'source_code_column' => 'source_code',
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['code']]],
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_composante_ens',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'individu-%s',
        'order' => 60,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/individu',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId']]],
            ],
            'column_name_filter' => [
                'supannId' => 'supann_id',
                'type' => 'type',
                'civilite' => 'civ',
                'nomUsuel' => 'lib_nom_usu_ind',
                'nomPatronymique' => 'lib_nom_pat_ind',
                'prenom1' => 'lib_pr1_ind',
                'prenom2' => 'lib_pr2_ind',
                'prenom3' => 'lib_pr3_ind',
                'email' => 'email',
                'dateNaissance' => 'dat_nai_per',
                'nationalite' => 'lib_nat',
                'codePaysNationalite' => 'cod_pay_nat', // à partir de la v2.1.0 du WS
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_individu',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'doctorant-%s',
        'order' => 70,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/doctorant',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','individuId']]],
            ],
            'column_name_filter' => [
                'individuId' => 'individu_id',
                'ine' => 'ine',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_doctorant',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'these-%s',
        'order' => 80,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/these',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','doctorantId','ecoleDoctId','uniteRechId']]],
            ],
            'column_name_filter' => [
                'doctorantId' => 'doctorant_id',
                'ecoleDoctId' => 'ecole_doct_id',
                'uniteRechId' => 'unite_rech_id',
                'title' => 'lib_ths',
                'dateSoutenanceAutorisee' => 'dat_aut_sou_ths',
                'dateConfidFin' => 'dat_fin_cfd_ths',
                'datePremiereInsc' => 'dat_deb_ths',
                'dateSoutenancePrev' => 'dat_prev_sou',
                'dateSoutenance' => 'dat_sou_ths',
                'dateAbandon' => 'dat_abandon',
                'dateTransfert' => 'dat_transfert_dep',
                'etatThese' => 'eta_ths',
                'codeSiseDiscipline' => 'code_sise_disc',
                'libDiscipline' => 'lib_int1_dis',
                'libEtabCotut' => 'lib_etab_cotut',
                'libPaysCotut' => 'lib_pays_cotut',
                'correctionAutorisee' => 'correction_possible',
                'correctionEffectuee' => 'correction_effectuee',
                'resultat' => 'cod_neg_tre',
                'temAvenant' => 'tem_avenant_cotut',
                'temSoutenanceAutorisee' => 'tem_sou_aut_ths',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_these',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'these-annee-univ-%s',
        'order' => 90,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/these-annee-univ',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','theseId']]],
            ],
            'column_name_filter' => [
                'theseId' => 'these_id',
                'anneeUniv' => 'annee_univ',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_these_annee_univ',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'role-%s',
        'order' => 100,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/role',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId']]],
            ],
            'column_name_filter' => [
                'libLongRole' => 'lib_roj',
                'libCourtRole' => 'lic_roj',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_role',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'acteur-%s',
        'order' => 110,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/acteur',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','individuId','roleId','theseId','acteurEtablissementId']]],
            ],
            'column_name_filter' => [
                'individuId' => 'individu_id',
                'theseId' => 'these_id',
                'roleId' => 'role_id',
                'acteurEtablissementId' => 'acteur_etablissement_id',
                'libQualite' => 'lib_cps',
                'codeQualite' => 'cod_cps',
                'codeRoleJury' => 'cod_roj_compl',
                'libRoleJury' => 'lib_roj_compl',
                'temoinHDR' => 'tem_hab_rch_per',
                'temoinRapport' => 'tem_rap_recu',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_acteur',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'origine-financement-%s',
        'order' => 120,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/origine-financement',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId']]],
            ],
            'column_name_filter' => [
                'codOfi' => 'cod_ofi',
                'licOfi' => 'lic_ofi',
                'libOfi' => 'lib_ofi',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_origine_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'financement-%s',
        'order' => 130,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/financement',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','theseId','origineFinancementId']]],
            ],
            'column_name_filter' => [
                'theseId' => 'these_id',
                'annee' => 'annee',
                'origineFinancementId' => 'origine_financement_id',
                'complementFinancement' => 'complement_financement',
                'quotiteFinancement' => 'quotite_financement',
                'dateDebutFinancement' => 'date_debut_financement',
                'dateFinFinancement' => 'date_fin_financement',
                'codeTypeFinancement' => 'code_type_financement',
                'libelleTypeFinancement' => 'libelle_type_financement',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'titre-acces-%s',
        'order' => 140,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/titre-acces',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId','theseId']]],
            ],
            'column_name_filter' => [
                'theseId' => 'these_id',
                'titreAccesInterneExterne' => 'titre_acces_interne_externe',
                'libelleTitreAcces' => 'libelle_titre_acces',
                'typeEtabTitreAcces' => 'type_etb_titre_acces',
                'libelleEtabTitreAcces' => 'libelle_etb_titre_acces',
                'codeDeptTitreAcces' => 'code_dept_titre_acces',
                'codePaysTitreAcces' => 'code_pays_titre_acces',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_titre_acces',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
    [
        'name' => 'variable-%s',
        'order' => 150,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/variable',
            'page_size' => 500,
            'column_value_filter' => [
                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['sourceCode','sourceId']]],
            ],
            'column_name_filter' => [
                'libEtablissement' => 'cod_vap',
                'libResponsable' => 'lib_vap',
                'libTitre' => 'par_vap',
                'dateDebValidite' => 'date_deb_validite',
                'dateFinValidite' => 'date_fin_validite',
                'sourceCode' => 'source_code',
                'sourceId' => 'source_id',
                'sourceInsertDate' => 'source_insert_date',
            ],
            'source_code_column' => 'source_code',
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'tmp_variable',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => null,
            'id_sequence' => null,
        ],
    ],
];

/**
 * Il y a une déclinaison automatique des synchros par établissement (pour pouvoir lancer la synchro pour un
 * établissement précis) : cf. fonction {@see generateConfigSynchrosForEtabs()} plus bas, appelée dans 'secret.local.php'.
 * Ce qui suit n'est que la config "commune".
 */
const CONFIG_SYNCHROS = [
    ////////////////////////////////////////////// STRUCTURE //////////////////////////////////////////////
    [
        ////// STRUCTURE : sans doublons non historisés.
        'name' => 'structure-%s',
        'order' => 11,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_structure',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'structure',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    ////////////////////////////////////////////// ETABLISSEMENT //////////////////////////////////////////////
    [
        ////// ETABLISSEMENT : sans doublons non historisés.
        'name' => 'etablissement-%s',
        'order' => 22,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_etablissement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'etablissement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    ////////////////////////////////////////////// ECOLE-DOCTORALE //////////////////////////////////////////////
    [
        ////// ECOLE-DOCTORALE : sans doublons non historisés.
        'name' => 'ecole-doctorale-%s',
        'order' => 31,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_ecole_doct',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'ecole_doct',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    ////////////////////////////////////////////// UNITE-RECHERCHE //////////////////////////////////////////////
    [
        ////// UNITE-RECHERCHE : sans doublons non historisés.
        'name' => 'unite-recherche-%s',
        'order' => 41,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_unite_rech',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'unite_rech',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    [
        'name' => 'composante-enseignement-%s',
        'order' => 50,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_composante_ens',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'composante_ens',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// INDIVIDU //////////////////////////////////////////////
    [
        ////// INDIVIDU : sans doublons non historisés.
        'name' => 'individu-%s',
        'order' => 60,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_individu',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'individu',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    ////////////////////////////////////////////// DOCTORANT //////////////////////////////////////////////
    [
        ////// DOCTORANT : sans doublons non historisés.
        'name' => 'doctorant-%s',
        'order' => 70,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_doctorant',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'doctorant',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
            'undelete_enabled_column' => 'synchro_undelete_enabled', // pour ne pas que les substitués soient déhistorisés
            'update_on_deleted_enabled_column' => 'synchro_update_on_deleted_enabled', // pour activer la màj des substitués (historisés)
        ],
    ],
    ////////////////////////////////////////////// THESE //////////////////////////////////////////////
    [
        'name' => 'these-%s',
        'order' => 80,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_these',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'these',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// THESE-ANNEE-UNIV //////////////////////////////////////////////
    [
        'name' => 'these-annee-univ-%s',
        'order' => 90,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_these_annee_univ',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'these_annee_univ',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// ROLE //////////////////////////////////////////////
    [
        'name' => 'role-%s',
        'order' => 100,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_role',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'role',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// ACTEUR //////////////////////////////////////////////
    [
        'name' => 'acteur-%s',
        'order' => 110,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_acteur',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'acteur',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// ORIGINE-FINANCEMENT //////////////////////////////////////////////
    [
        'name' => 'origine-financement-%s',
        'order' => 120,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_origine_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'origine_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// FINANCEMENT //////////////////////////////////////////////
    [
        'name' => 'financement-%s',
        'order' => 130,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// TITRE-ACCES //////////////////////////////////////////////
    [
        'name' => 'titre-acces-%s',
        'order' => 140,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_titre_acces',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'titre_acces',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
    ////////////////////////////////////////////// VARIABLE //////////////////////////////////////////////
    [
        'name' => 'variable-%s',
        'order' => 150,
        'source' => [
            'name' => 'sygal',
            'code' => 'app',
            'table' => 'src_variable',
            'connection' => 'default',
            'source_code_column' => 'source_code',
        ],
        'destination' => [
            'name' => 'application',
            'table' => 'variable',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_strategy' => 'SEQUENCE',
            'id_sequence' => null,
        ],
    ],
];

/**
 * Déclinaison des imports par établissement (pour pouvoir lancer l'import pour un établissement précis).
 * @param array $etabs
 * @return array
 */
function generateConfigImportsForEtabs(array $etabs): array
{
    $synchros = [];
    foreach ($etabs as $etab) {
        foreach (CONFIG_IMPORTS as $array) {
            $array['name'] = generateNameForEtab($array['name'], $etab);
            $array['source']['name'] = generateNameForEtab($array['source']['name'], $etab);
            $array['source']['connection'] = generateNameForEtab($array['source']['connection'], $etab);
            $array['source']['extra'] = [
                PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $etab,
            ];
            $synchros[] = $array;
        }
    }
    return $synchros;
}

/**
 * Déclinaison des synchros par établissement (pour pouvoir lancer la synchro pour un établissement précis).
 * @param array $etabs
 * @return array
 */
function generateConfigSynchrosForEtabs(array $etabs): array
{
    $synchros = [];
    foreach ($etabs as $etab) {
        foreach (CONFIG_SYNCHROS as $array) {
            $array['name'] = generateNameForEtab($array['name'], $etab);
            $array['destination']['where'] = generateWhereForEtab($etab);
            $synchros[] = $array;
        }
    }
    return $synchros;
}

/**
 * Injecte le code établissement dans un nom.
 * @param string $nameTemplate Nom avec "emplacement" pour le code établissement, ex : 'individu-%s'
 * @param string $codeEtablissement Code établissement, ex : 'UCN'
 * @return string Ex : "individu-UCN"
 */
function generateNameForEtab(string $nameTemplate, string $codeEtablissement): string
{
    return sprintf($nameTemplate,  $codeEtablissement);
}

/**
 * Génère la clause à utiliser dans un WHERE pour cibler un établissement précis.
 * @param string $codeEtablissement Code établissement maison unique, ex : 'UCN', 'URN', etc.
 * @return string Ex : "d.source_id = (... like 'UCN::%')"
 */
function generateWhereForEtab(string $codeEtablissement): string
{
    return <<<EOS
d.source_id in (
    select s.id from source s 
    join etablissement e on s.etablissement_id = e.id
    where e.source_code = '$codeEtablissement'
)
EOS;
}


return $config;
