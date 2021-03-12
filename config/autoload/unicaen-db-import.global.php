<?php

use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;

return [
    'import' => [

        'import_observ_entity_class' => ImportObserv::class,
        'import_observ_result_entity_class' => ImportObservResult::class,

        'connections' => [
            // Cf. config locale
        ],

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

        ],

        //
        // Synchros.
        //
        'synchros' => [
            [
                'name' => 'structure',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_STRUCTURE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'STRUCTURE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_STRUCTURE',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'etablissement',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_ETABLISSEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'ETABLISSEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_ETABLISSEMENT',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'ecole-doctorale',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_ECOLE_DOCT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'ECOLE_DOCT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_ECOLE_DOCT',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'unite-recherche',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_UNITE_RECH',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'UNITE_RECH',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_UNITE_RECH',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'individu',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_INDIVIDU',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'INDIVIDU',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_INDIVIDU',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'doctorant',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_DOCTORANT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'DOCTORANT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_DOCTORANT',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'these',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_THESE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'THESE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_THESE',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'these-annee-univ',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_THESE_ANNEE_UNIV',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'THESE_ANNEE_UNIV',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_THESE_ANNEE_UNIV',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'role',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_ROLE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'ROLE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_ROLE',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'acteur',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_ACTEUR',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'ACTEUR',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_ACTEUR',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'origine-financement',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_ORIGINE_FINANCEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'ORIGINE_FINANCEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_ORIGINE_FINANCEMENT',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'financement',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_FINANCEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'FINANCEMENT',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_FINANCEMENT',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'titre-acces',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_TITRE_ACCES',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'TITRE_ACCES',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_TITRE_ACCES',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
            [
                'name' => 'variable',
                'source' => [
                    'name'               => 'app',
                    'table'              => 'SRC_VARIABLE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                ],
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'VARIABLE',
                    'connection'         => 'default',
                    'source_code_column' => 'SOURCE_CODE',
                    //'log_table' => 'import_log',
                    //'intermediate_table' => 'TMP_VARIABLE',
                    'intermediate_table_auto_drop' => false,
                ],
            ],
        ],
    ],
];