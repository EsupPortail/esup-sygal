<?php

namespace Application;

use Import\Filter\PrefixEtabColumnValueFilter;

/**
 * todo : à déplacer peut-être dans le module Import...
 */
class Config
{
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
                'page_size' => 500,
                'column_value_filter' => [
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'structureId']],
                ],
                'column_name_filter' => [
                    'structureId' => 'structure_id',
                    'sourceCode' => 'source_code',
                    'sourceId' => 'source_id',
                    'sourceInsertDate' => 'source_insert_date',
                ],
                'source_code_column' => 'source_code',
                'extra' => [
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'structureId']],
                ],
                'column_name_filter' => [
                    'structureId' => 'structure_id',
                    'sourceCode' => 'source_code',
                    'sourceId' => 'source_id',
                    'sourceInsertDate' => 'source_insert_date',
                ],
                'source_code_column' => 'source_code',
                'extra' => [
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'structureId']],
                ],
                'column_name_filter' => [
                    'structureId' => 'structure_id',
                    'sourceCode' => 'source_code',
                    'sourceId' => 'source_id',
                    'sourceInsertDate' => 'source_insert_date',
                ],
                'source_code_column' => 'source_code',
                'extra' => [
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
//            'code' => 'UCN::octopus',
//            'page_size' => 0,
//            'columns' => [
//                'sigle',
//                'libelleLong',
//                'code',
//            ],
//            'column_name_filter' => [
//                'sigle' => 'sigle',
//                'libelleLong' => 'libelle_long',
//                'code' => 'source_code',
//            ],
//            'source_code_column' => 'source_code',
//            'column_value_filter' => [
//                ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['columns' => ['code']]],
//            ],
//            'extra' => [
//                /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
//            ],
//        ],
//        'destination' => [
//            'name' => 'application',
//            'table' => 'tmp_composante_ens',
//            'connection' => 'default',
//            'source_code_column' => 'source_code',
//            'id_strategy' => null,
//            'id_sequence' => null,
//        ],
//    ],
        [
            'name' => 'individu-%s',
            'order' => 60,
            'source' => [
                'name' => '%s',
                'connection' => 'sygal-import-ws-%s',
                'select' => '/individu',
                'page_size' => 500,
                'column_value_filter' => [
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'individuId']],
                ],
                'column_name_filter' => [
                    'individuId' => 'individu_id',
                    'ine' => 'ine',
                    'codeApprenantInSource' => 'code_apprenant_in_source',
                    'sourceCode' => 'source_code',
                    'sourceId' => 'source_id',
                    'sourceInsertDate' => 'source_insert_date',
                ],
                'source_code_column' => 'source_code',
                'extra' => [
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'doctorantId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'ecoleDoctId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'uniteRechId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'theseId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'individuId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'roleId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'theseId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'acteurEtablissementId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'theseId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'origineFinancementId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'theseId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceCode']],
                    ['name' => PrefixEtabColumnValueFilter::class, 'params' => ['column' => 'sourceId']],
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
                    /** cf. injection dans {@see \Application\Config::generateConfigImportsForEtabs()} */
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
     * établissement précis) : cf. fonction {@see \Application\Config::generateConfigSynchrosForEtabs()}, appelée dans 'secret.local.php'.
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
//    [
//        'name' => 'composante-enseignement-%s',
//        'order' => 50,
//        'source' => [
//            'name' => 'sygal',
//            'code' => 'app',
//            'table' => 'src_composante_ens',
//            'connection' => 'default',
//            'source_code_column' => 'source_code',
//        ],
//        'destination' => [
//            'name' => 'application',
//            'table' => 'composante_ens',
//            'connection' => 'default',
//            'source_code_column' => 'source_code',
//            'id_strategy' => 'SEQUENCE',
//            'id_sequence' => null,
//        ],
//    ],
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
    static public function generateConfigImportsForEtabs(array $etabs): array
    {
        $imports = [];
        foreach ($etabs as $etab) {
            foreach (static::CONFIG_IMPORTS as $array) {
                $array['name'] = static::generateNameForEtab($array['name'], $etab);
                $array['source']['name'] = static::generateNameForEtab($array['source']['name'], $etab);
                $array['source']['connection'] = static::generateNameForEtab($array['source']['connection'], $etab);
                $array['source']['extra'] = [
                    PrefixEtabColumnValueFilter::PARAM_CODE_ETABLISSEMENT => $etab,
                ];
                $imports[] = $array;
            }
        }
        return $imports;
    }

    /**
     * Déclinaison des synchros par établissement (pour pouvoir lancer la synchro pour un établissement précis).
     * @param array $etabs
     * @return array
     */
    static public function generateConfigSynchrosForEtabs(array $etabs): array
    {
        $synchros = [];
        foreach ($etabs as $etab) {
            foreach (static::CONFIG_SYNCHROS as $array) {
                $array['name'] = static::generateNameForEtab($array['name'], $etab);
                $array['destination']['where'] = static::generateWhereForEtab($etab);
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
    static public function generateNameForEtab(string $nameTemplate, string $codeEtablissement): string
    {
        return sprintf($nameTemplate,  $codeEtablissement);
    }

    /**
     * Génère la clause à utiliser dans un WHERE pour cibler un établissement précis.
     * @param string $codeEtablissement Code établissement maison unique, ex : 'UCN', 'URN', etc.
     * @return string Ex : "d.source_id = (... like 'UCN::%')"
     */
    static public function generateWhereForEtab(string $codeEtablissement): string
    {
        return <<<EOS
d.source_id in (
    select s.id from source s 
    join etablissement e on s.etablissement_id = e.id
    where e.source_code = '$codeEtablissement'
)
EOS;
    }

}