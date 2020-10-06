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
            [
                //
                // Nom unique de l'import.
                //
                'name' => "IMPORT_PAYS",
                //
                // Configuration de la source de données à importer :
                // - 'name'               : nom unique de la source
                // - 'connection'         : identifiant de la connexion à la bdd source (cf. clé 'connections' plus haut)
                // - 'table'              : nom de la table source contenant les données à importer
                // - 'select'             : select SQL de mise en forme des données source à importer (NB: antinomique avec 'table')
                // - 'source_code_column' : nom de la colonne dans la table/vue source contenant l'identifiant unique
                //
                'source' => [
                    'name'               => 'UCN::apogee',
                    'select'             => "select CODE_PAYS, LIBELLE_COURT, LIBELLE_LONG, CODE_ISO2, CODE_ISO3, CODE_ISO_NUM, DATE_OUVERTURE, DATE_FERMETURE FROM PAYS",
                    'connection'         => 'octopus',
                    'source_code_column' => 'CODE_PAYS',
                ],
                //
                // Configuration de la destination des données importées :
                // - 'name'               : nom unique de la destination
                // - 'connection'         : identifiant de la connexion à la bdd destination (cf. clé 'connections' plus haut)
                // - 'table'              : nom de la table destination vers laquelle les données sont importées
                // - 'source_code_column' : nom de la colonne dans la table destination contenant l'identifiant unique
                //
                'destination' => [
                    'name'               => 'app',
                    'table'              => 'TMP_PAYS',
                    'connection'         => 'default',
                    'source_code_column' => 'CODE_PAYS',
                    //
                    // Forçage éventuel du nom de la séquence permettant de générer les valeurs dans la colonne ID (clé primaire).
                    // En l'absence de forçage, aucune séquence ne sera utilisée et la colonne devra avoir un type auto-incrémenté
                    // pour que l'insertion de données fonctionne.
                    //
                    'id_sequence' => false,
                ],
            ],
        ],

        //
        // Synchros.
        //
        'synchros' => [
            [
                'name' => 'SYNCHRO_STRUCTURE',
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
                'name' => 'SYNCHRO_ETABLISSEMENT',
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
                'name' => 'SYNCHRO_ECOLE_DOCT',
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
                'name' => 'SYNCHRO_UNITE_RECH',
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
                'name' => 'SYNCHRO_INDIVIDU',
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
                'name' => 'SYNCHRO_DOCTORANT',
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
                'name' => 'SYNCHRO_THESE',
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
                'name' => 'SYNCHRO_THESE_ANNEE_UNIV',
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
                'name' => 'SYNCHRO_ROLE',
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
                'name' => 'SYNCHRO_ACTEUR',
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
                'name' => 'SYNCHRO_FINANCEMENT',
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
                'name' => 'SYNCHRO_TITRE_ACCES',
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
                'name' => 'SYNCHRO_VARIABLE',
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