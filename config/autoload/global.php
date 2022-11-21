<?php

namespace Application;

use Application\Entity\Db\Source;
use Application\Navigation\NavigationFactoryFactory;
use Import\Filter\PrefixEtabColumnValueFilter;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Retraitement\Filter\Command\RetraitementShellCommandMines;

define('APPLICATION_DIR', __DIR__ . '/../..');

$config = [
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
            'created_on' => 'HISTO_CREATION',     // date/heure de création de l'enregistrement
            'updated_on' => 'HISTO_MODIFICATION', // date/heure de modification
            'deleted_on' => 'HISTO_DESTRUCTION',  // date/heure de suppression

            'created_by' => 'HISTO_CREATEUR_ID',     // auteur de la création de l'enregistrement
            'updated_by' => 'HISTO_MODIFICATEUR_ID', // auteur de la modification
            'deleted_by' => 'HISTO_DESTRUCTEUR_ID',  // auteur de la suppression
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
            // <==== la config des imports sera injectée ici
        ],

        //
        // Synchros.
        //
        'synchros' => [
            // <==== la config des synchros sera injectée ici
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
        'name' => 'structure-%s',
        'order' => 10,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/structure',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'typeStructureId' => 'TYPE_STRUCTURE_ID',
                'sigle' => 'SIGLE',
                'libelle' => 'LIBELLE',
                'codePays' => 'CODE_PAYS',
                'libellePays' => 'LIBELLE_PAYS',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_structure',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'etablissement-%s',
        'order' => 20,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/etablissement',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'structureId' => 'STRUCTURE_ID',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_etablissement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'ecole-doctorale-%s',
        'order' => 30,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/ecole-doctorale',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'structureId' => 'STRUCTURE_ID',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_ecole_doct',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'unite-recherche-%s',
        'order' => 40,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/unite-recherche',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'structureId' => 'STRUCTURE_ID',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_unite_rech',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'individu-%s',
        'order' => 50,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/individu',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'supannId' => 'SUPANN_ID',
                'type' => 'TYPE',
                'civilite' => 'CIV',
                'nomUsuel' => 'LIB_NOM_USU_IND',
                'nomPatronymique' => 'LIB_NOM_PAT_IND',
                'prenom1' => 'LIB_PR1_IND',
                'prenom2' => 'LIB_PR2_IND',
                'prenom3' => 'LIB_PR3_IND',
                'email' => 'EMAIL',
                'dateNaissance' => 'DAT_NAI_PER',
                'nationalite' => 'LIB_NAT',
                'codePaysNationalite' => 'cod_pay_nat', // à partir de la v2.1.0 du WS
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_individu',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'doctorant-%s',
        'order' => 60,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/doctorant',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'individuId' => 'INDIVIDU_ID',
                'ine' => 'INE',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_doctorant',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'these-%s',
        'order' => 70,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/these',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'doctorantId' => 'DOCTORANT_ID',
                'ecoleDoctId' => 'ECOLE_DOCT_ID',
                'uniteRechId' => 'UNITE_RECH_ID',
                'title' => 'LIB_THS',
                'dateSoutenanceAutorisee' => 'DAT_AUT_SOU_THS',
                'dateConfidFin' => 'DAT_FIN_CFD_THS',
                'datePremiereInsc' => 'DAT_DEB_THS',
                'dateSoutenancePrev' => 'DAT_PREV_SOU',
                'dateSoutenance' => 'DAT_SOU_THS',
                'dateAbandon' => 'DAT_ABANDON',
                'dateTransfert' => 'DAT_TRANSFERT_DEP',
                'etatThese' => 'ETA_THS',
                'codeSiseDiscipline' => 'CODE_SISE_DISC',
                'libDiscipline' => 'LIB_INT1_DIS',
                'libEtabCotut' => 'LIB_ETAB_COTUT',
                'libPaysCotut' => 'LIB_PAYS_COTUT',
                'correctionAutorisee' => 'CORRECTION_POSSIBLE',
                'correctionEffectuee' => 'CORRECTION_EFFECTUEE',
                'resultat' => 'COD_NEG_TRE',
                'temAvenant' => 'TEM_AVENANT_COTUT',
                'temSoutenanceAutorisee' => 'TEM_SOU_AUT_THS',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_these',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'these-annee-univ-%s',
        'order' => 80,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/these-annee-univ',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'theseId' => 'THESE_ID',
                'anneeUniv' => 'ANNEE_UNIV',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_these_annee_univ',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'role-%s',
        'order' => 90,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/role',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'libLongRole' => 'LIB_ROJ',
                'libCourtRole' => 'LIC_ROJ',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_role',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'acteur-%s',
        'order' => 100,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/acteur',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'individuId' => 'INDIVIDU_ID',
                'theseId' => 'THESE_ID',
                'roleId' => 'ROLE_ID',
                'acteurEtablissementId' => 'ACTEUR_ETABLISSEMENT_ID',
                'libQualite' => 'LIB_CPS',
                'codeQualite' => 'COD_CPS',
                'codeRoleJury' => 'COD_ROJ_COMPL',
                'libRoleJury' => 'LIB_ROJ_COMPL',
                'temoinHDR' => 'TEM_HAB_RCH_PER',
                'temoinRapport' => 'TEM_RAP_RECU',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_acteur',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'origine-financement-%s',
        'order' => 110,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/origine-financement',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'codOfi' => 'COD_OFI',
                'licOfi' => 'LIC_OFI',
                'libOfi' => 'LIB_OFI',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_origine_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'financement-%s',
        'order' => 120,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/financement',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'theseId' => 'THESE_ID',
                'annee' => 'ANNEE',
                'origineFinancementId' => 'ORIGINE_FINANCEMENT_ID',
                'complementFinancement' => 'COMPLEMENT_FINANCEMENT',
                'quotiteFinancement' => 'QUOTITE_FINANCEMENT',
                'dateDebutFinancement' => 'DATE_DEBUT_FINANCEMENT',
                'dateFinFinancement' => 'DATE_FIN_FINANCEMENT',
                'codeTypeFinancement' => 'CODE_TYPE_FINANCEMENT',
                'libelleTypeFinancement' => 'LIBELLE_TYPE_FINANCEMENT',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_financement',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'titre-acces-%s',
        'order' => 130,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/titre-acces',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'theseId' => 'THESE_ID',
                'titreAccesInterneExterne' => 'TITRE_ACCES_INTERNE_EXTERNE',
                'libelleTitreAcces' => 'LIBELLE_TITRE_ACCES',
                'typeEtabTitreAcces' => 'TYPE_ETB_TITRE_ACCES',
                'libelleEtabTitreAcces' => 'LIBELLE_ETB_TITRE_ACCES',
                'codeDeptTitreAcces' => 'CODE_DEPT_TITRE_ACCES',
                'codePaysTitreAcces' => 'CODE_PAYS_TITRE_ACCES',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_titre_acces',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
    [
        'name' => 'variable-%s',
        'order' => 140,
        'source' => [
            'name' => '%s',
            'connection' => 'sygal-import-ws-%s',
            'select' => '/variable',
            'source_code_column' => 'SOURCE_CODE',
            'page_size' => 500,
            'column_value_filter' => PrefixEtabColumnValueFilter::class,
            'column_name_filter' => [
                'libEtablissement' => 'COD_VAP',
                'libResponsable' => 'LIB_VAP',
                'libTitre' => 'PAR_VAP',
                'dateDebValidite' => 'DATE_DEB_VALIDITE',
                'dateFinValidite' => 'DATE_FIN_VALIDITE',
                'sourceCode' => 'SOURCE_CODE',
                'sourceId' => 'SOURCE_ID',
                'sourceInsertDate' => 'SOURCE_INSERT_DATE',
            ],
            'extra' => [
                /** cf. injection dans {@see \Application\generateConfigImportsForEtabs()} */
            ],
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'tmp_variable',
            'connection' => 'default',
            'source_code_column' => 'source_code',
            'id_sequence' => false,
        ],
    ],
];

/**
 * Il y a une déclinaison automatique des synchros par établissement (pour pouvoir lancer la synchro pour un
 * établissement précis) : cf. fonction {@see generateConfigSynchrosForEtabs()} plus bas, appelée dans 'secret.local.php'.
 * Ce qui suit n'est que la config "commune".
 */
const CONFIG_SYNCHROS = [
    [
        'name' => 'structure-%s',
        'order' => 10,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_STRUCTURE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'STRUCTURE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'etablissement-%s',
        'order' => 20,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_ETABLISSEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'ETABLISSEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'ecole-doctorale-%s',
        'order' => 30,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_ECOLE_DOCT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'ECOLE_DOCT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'unite-recherche-%s',
        'order' => 40,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_UNITE_RECH',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'UNITE_RECH',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'individu-%s',
        'order' => 50,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_INDIVIDU',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'INDIVIDU',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
//            'where'              => "d.source_code like 'UCN::%'", // todo: à virer
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'doctorant-%s',
        'order' => 60,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_DOCTORANT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'DOCTORANT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'these-%s',
        'order' => 70,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_THESE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'THESE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'these-annee-univ-%s',
        'order' => 80,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_THESE_ANNEE_UNIV',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'THESE_ANNEE_UNIV',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'role-%s',
        'order' => 90,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_ROLE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'ROLE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'acteur-%s',
        'order' => 100,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_ACTEUR',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'ACTEUR',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'origine-financement-%s',
        'order' => 110,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_ORIGINE_FINANCEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'ORIGINE_FINANCEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'financement-%s',
        'order' => 120,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_FINANCEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'FINANCEMENT',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'titre-acces-%s',
        'order' => 130,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_TITRE_ACCES',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'TITRE_ACCES',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
        ],
    ],
    [
        'name' => 'variable-%s',
        'order' => 140,
        'source' => [
            'name' => 'SyGAL',
            'code' => 'app',
            'table' => 'SRC_VARIABLE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
        ],
        'destination' => [
            'name' => 'Application',
            'table' => 'VARIABLE',
            'connection' => 'default',
            'source_code_column' => 'SOURCE_CODE',
            'intermediate_table_auto_drop' => false,
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
 * @param string $codeEtablissement Code établissement, ex : 'UCN'
 * @return string Ex : "d.source_id = (... like 'UCN::%')"
 */
function generateWhereForEtab(string $codeEtablissement): string
{
    return sprintf("d.source_id = (select id from source where code like '%s::%%')", $codeEtablissement);
}


return $config;
