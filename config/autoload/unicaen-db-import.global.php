<?php

use Application\Entity\Db\Source;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;

/**
 * Il y a une déclinaison automatique des synchros par établissement (pour pouvoir lancer la synchro pour un
 * établissement précis) : cf. fonction {@see generateConfigSynchros()} plus bas, appelée dans 'secret.local.php'.
 * Ce qui suit n'est que la config "générique".
 */

const CONFIG_SYNCHROS = [
    'structure' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'etablissement' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'ecole-doctorale' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'unite-recherche' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'individu' => [
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
//            'where'              => "d.source_code like 'UCN::%'", // todo: à virer
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'doctorant' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'these' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'these-annee-univ' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'role' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'acteur' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'origine-financement' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'financement' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'titre-acces' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
    'variable' => [
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
            'intermediate_table_auto_drop' => false,
        ],
    ],
];

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
        'synchros' => [

        ],
    ],
];


/**
 * Déclinaison des synchros par établissement (pour pouvoir lancer la synchro pour un établissement précis) :
 * - renommage de chaque synchro,
 * - ajout d'un 'where' à chaque destination de synchro.
 *
 * @param array $etabs
 * @return array
 */
function generateConfigSynchros(array $etabs): array
{
    $synchros = [];
    foreach ($etabs as $etab) {
        foreach (CONFIG_SYNCHROS as $array) {
            $array['name'] = generateSynchroName($array['name'], $etab);                    // ex : "individu-UCN"
            $array['destination']['where'] = sprintf("d.source_code like '%s::%%'", $etab); // ex : "d.source_code like 'UCN::%'"
            $synchros[] = $array;
        }
    }
    return $synchros;
}

/**
 * Génère le nom complet d'une synchro.
 *
 * @param string $serviceName Nom du service, ex : 'individu'
 * @param string $codeEtablissement Code établissement, ex : 'UCN'
 * @return string
 */
function generateSynchroName(string $serviceName, string $codeEtablissement): string
{
    return $serviceName . '-' .  $codeEtablissement;
}

