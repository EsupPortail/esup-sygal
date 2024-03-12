<?php

namespace Substitution\Service\ForeignKey;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

class ForeignKeyService
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllForeignKeysForType(string $type): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllForeignKeys($type)
        );
    }

    private function generateSqlToFindAllForeignKeys(string $type): string
    {
        return <<<EOS
select * from v_substit_foreign_keys_{$type}
order by source_table, fk_column
EOS;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllRelatedRecordsForTypeAndForeignKeyValue(string $type, int $substituantId): Result
    {
        $sql = $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllRelatedRecords($type, $substituantId)
        )->fetchOne();

        $sql .= ' order by source_table, id';

        return $this->entityManager->getConnection()->executeQuery($sql);
    }

    private function generateSqlToFindAllRelatedRecords(string $type, int $substituantId): string
    {
        return <<<EOS
select string_agg(
    'select '''||source_table||''' source_table, '''||fk_column||' = {$substituantId}'', '||source_table||'.id id, '||source_table||'::text data '||
    'from '||source_table||' where '||fk_column||' = {$substituantId}', 
    ' UNION '||chr(10)
)
from v_substit_foreign_keys_{$type} 
EOS;
    }
}