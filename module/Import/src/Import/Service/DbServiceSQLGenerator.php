<?php

namespace Import\Service;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class DbServiceSQLGenerator
{
    /**
     * @var AbstractPlatform
     */
    private $databasePlatform;

    /**
     * @param AbstractPlatform $databasePlatform
     * @return self
     */
    public function setDatabasePlatform($databasePlatform)
    {
        $this->databasePlatform = $databasePlatform;

        return $this;
    }

    /**
     * Génère la requête SQL de suppression de tout ou partie des données déjà importées.
     *
     * @param string $tableName
     * @param array $filters
     * @return string
     */
    public function generateSQLQueryForClearingExistingData($tableName, array $filters = [])
    {
        $query = "DELETE FROM " . $tableName;

        if (count($filters) > 0) {
            $wheres = $filters;
            // NB: préfixage par le code établissement doit être fait en amont
            array_walk($wheres, function (&$v, $k) {
                $v = strtoupper($k) . " = '$v'";
            });
            $wheres = implode(' AND ', $wheres);
            $query .= ' WHERE ' . $wheres;
        }

        return $query;
    }

    /**
     * Génère les requêtes SQL de persistence d'une donnée importée.
     *
     * @param string $tableName
     * @param array $tableColumns
     * @param array $columnsValues
     * @return string
     */
    public function generateSQLQueryForSavingData($tableName, array $tableColumns, array $columnsValues)
    {
        return sprintf("INSERT INTO %s (%s) VALUES (%s)",
            $tableName,
            implode(", ", $tableColumns),
            implode(", ", $columnsValues)
        );
    }

    /**
     * @param array $queries
     * @return string
     */
    public function wrapSQLQueriesInBeginEnd(array $queries)
    {
        $indent = '  ';

        $sql = $indent . implode(';' . PHP_EOL . $indent, $queries) . ';';

        return implode(PHP_EOL, ['DO $$ BEGIN', $sql, 'END $$;']);
    }

    /**
     * Fonction de mise en forme d'une valeur selon les metatadata de la propriété spécifiée.
     *
     * @param mixed $value La valeur à formater
     * @param string $type
     * @return string La donnée formatée
     *
     * RMQ si un format n'est pas prévu par le traitement la valeur est retournée sans traitement et un message est
     * affiché
     */
    public function formatValueForPropertyType($value, $type)
    {
        switch ($type) {
            case 'string':
                return $this->prepareString($value);
            case 'date':
                return $this->prepareDate($value);
            case 'datetime':
                return $this->prepareDatetime($value);
            default:
                return $value;
        }
    }

    private function prepareString($value)
    {
        if ($value === null) {
            return 'NULL';
        }

        return $this->databasePlatform->quoteStringLiteral($value);
    }

    private function prepareDate($value)
    {
        if ($value === null) {
            return "NULL";
        }

        $yyymmdd = explode(' ', $value->date)[0];

        return "to_date('$yyymmdd', 'YYYY-MM-DD')";
    }

    private function prepareDatetime($value)
    {
        if ($value === null) {
            return "NULL";
        }

        $yyymmddhhmiss = explode('.', $value->date)[0];

        return "to_timestamp('$yyymmddhhmiss', 'YYYY-MM-DD HH24:MI:SS')";
    }
}
