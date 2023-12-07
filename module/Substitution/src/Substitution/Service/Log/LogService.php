<?php

namespace Substitution\Service\Log;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

class LogService
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllLogsForType(string $type, array $params = [], int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllLogs($type, $limit, $params)
        );
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDistinctLogsOperationsForType(string $type): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindDistinctLogsOperations($this->generateSqlToFindAllLogs($type))
        );
    }

    private function generateSqlToFindAllLogs(string $type, int $limit = 0, array $params = []): string
    {
        $operation = $params['operation'] ?? null;
        $andWhereOperation = ($operation && $operation !== '*') ? "and operation = '$operation'" : null;
        $limit = $limit > 0 ? "limit $limit" : null;

        return <<<EOS
select id, type, operation, substitue_id, substituant_id, npd, log, to_char(created_on,'DD/MM/YYYY HH24:MI:SS') created_on
from substit_log
where type = '{$type}' $andWhereOperation
order by id desc
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