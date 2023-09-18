<?php

namespace Substitution\Service;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

class LogService
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsIndividu(array $params = []): Result
    {
        return $this->_findAllLogs('individu', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsDoctorant(array $params = []): Result
    {
        return $this->_findAllLogs('doctorant', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsStructure(array $params = []): Result
    {
        return $this->_findAllLogs('structure', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsEtablissement(array $params = []): Result
    {
        return $this->_findAllLogs('etablissement', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsEcoleDoct(array $params = []): Result
    {
        return $this->_findAllLogs('ecole_doct', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsUniteRech(array $params = []): Result
    {
        return $this->_findAllLogs('unite_rech', 1000, $params);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function _findAllLogs(string $type, int $limit = 0, array $params = []): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllLogs($type, $limit, $params)
        );
    }



    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsIndividu(): Result
    {
        return $this->_findDistinctLogsOperations('individu');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsDoctorant(): Result
    {
        return $this->_findDistinctLogsOperations('doctorant');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsStructure(): Result
    {
        return $this->_findDistinctLogsOperations('structure');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsEtablissement(): Result
    {
        return $this->_findDistinctLogsOperations('etablissement');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsEcoleDoct(): Result
    {
        return $this->_findDistinctLogsOperations('ecole_doct');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsUniteRech(): Result
    {
        return $this->_findDistinctLogsOperations('unite_rech');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function _findDistinctLogsOperations(string $type): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindDistinctLogsOperations($this->generateSqlToFindAllLogs($type))
        );
    }


    //------------------------------------------------------------------------------------------------------------
    // SQL foreign keys
    //------------------------------------------------------------------------------------------------------------

    private function generateSqlToFindAllLogs(string $type, int $limit = 0, array $params = []): string
    {
        $operation = $params['operation'] ?? null;
        $andWhereOperation = ($operation && $operation !== '*') ? "and operation = '$operation'" : null;
        $limit = $limit > 0 ? "limit $limit" : null;

        return <<<EOS
select id, type, operation, substitue_id, substituant_id, npd, log, to_char(created_on,'DD/MM/YYYY HH24:MI:SS') created_on
from substit_log
where type = '{$type}' $andWhereOperation
order by created_on desc, id desc
$limit
EOS;
    }

    private function generateSqlToFindDistinctLogsOperations(string $selectSql): string
    {
        return <<<EOS
select distinct operation from ($selectSql) tmp
order by operation
EOS;
    }
}