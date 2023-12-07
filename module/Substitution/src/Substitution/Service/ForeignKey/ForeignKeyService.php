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
}