<?php

use Application\Entity\Db\Source;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;

// Déclinaison des synchros par établissement (pour pouvoir lancer la synchro pour un établissement précis).
// ==> ajout d'un 'where' à toutes les destinations des synchros.
$synchrosProto = [
    [
        'name' => 'structure-%s', // <<< Sera décliné en 'structure-UCN', 'structure-URN', etc.
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'", // <<< Sera déclinée en "d.source_code like 'UCN::%'", etc.
        ],
    ],
    [
        'name' => 'etablissement-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'ecole-doctorale-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'unite-recherche-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'individu-%s',
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
//            'where'              => "d.source_code like 'UCN::%'", // todo: à virer
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'doctorant-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'these-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'these-annee-univ-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'role-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'acteur-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'origine-financement-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'financement-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'titre-acces-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
    [
        'name' => 'variable-%s',
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
            'intermediate_table_auto_drop' => false,
            'where' => "d.source_code like '%s::%%'",
        ],
    ],
];
$etabs = ['UCN', 'URN', 'ULHN', 'INSA'];
$synchros = [];
foreach ($etabs as $etab) {
    foreach ($synchrosProto as $proto) {
        $proto['name'] = sprintf($proto['name'], $etab);                                 // ex : "individu-UCN"
        $proto['destination']['where'] = sprintf($proto['destination']['where'], $etab); // ex : "d.source_code like 'UCN::%'"
        $synchros[] = $proto;
    }
}

return [
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

        ],

        //
        // Synchros.
        //
        'synchros' => $synchros,
    ],
];