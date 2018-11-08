<?php

namespace Import\Service;

class SchemaService extends \UnicaenImport\Service\SchemaService
{
    static public $ordreTables = [
        'STRUCTURE'     => 'a',
        'ETABLISSEMENT' => 'b',
        'ECOLE_DOCT'    => 'c',
        'UNITE_RECH'    => 'd',
        'INDIVIDU'      => 'e',
        'DOCTORANT'     => 'f',
        'THESE'         => 'g',
        'ROLE'          => 'h',
        'ACTEUR'        => 'i',
        'VARIABLE'      => 'j',
    ];

    public function getImportTables()
    {
        $tables = parent::getImportTables();

        $tables = array_filter($tables, function ($table) {
            return ! in_array($table, [
                'SYNC_LOG', // ne devrait pas apparaître mais bon...
            ]);
        });

        $tables = $this->sortTablesData(array_combine($tables, $tables));

        return array_values($tables);
    }

    public function makeSchema()
    {
        $sc = parent::makeSchema();

        $keys = [
            'SYNC_LOG', // ne devrait pas apparaître mais bon...
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $sc)) {
                unset($sc[$key]);
            }
        }

        return $this->sortTablesData($sc);
    }

    /**
     * Tri d'un tableau de données concernant des tables.
     * NB: le tri se fait sur les clés du tableau, qui doivent être des noms de tables.
     *
     * @param array $data 'TABLE' => [...]
     * @return array
     */
    public function sortTablesData($data)
    {
        $temp = $data;
        $ordreTables = self::$ordreTables;

        uksort($temp, function($a, $b) use ($ordreTables) {
            return strcmp(
                isset($ordreTables[$a]) ? $ordreTables[$a] : 'zzz',
                isset($ordreTables[$b]) ? $ordreTables[$b] : 'zzz'
            );
        });

        return $temp;
    }

}