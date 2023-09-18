<?php

namespace Substitution\Service;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ForeignKeyService
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysIndividu(): Result
    {
        return $this->_findAllForeignKeys('individu');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysDoctorant(): Result
    {
        return $this->_findAllForeignKeys('doctorant');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysStructure(): Result
    {
        return $this->_findAllForeignKeys('structure');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysEtablissement(): Result
    {
        return $this->_findAllForeignKeys('etablissement');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysEcoleDoct(): Result
    {
        return $this->_findAllForeignKeys('ecole_doct');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysUniteRech(): Result
    {
        return $this->_findAllForeignKeys('unite_rech');
    }


    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function _findAllForeignKeys(string $type): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllForeignKeys($type)
        );
    }


    //------------------------------------------------------------------------------------------------------------
    // SQL foreign keys
    //------------------------------------------------------------------------------------------------------------

    private function generateSqlToFindAllForeignKeys(string $type): string
    {
        return <<<EOS
select * from v_substit_foreign_keys_{$type}
order by source_table, fk_column
EOS;
    }
}