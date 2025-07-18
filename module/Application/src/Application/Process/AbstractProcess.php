<?php

namespace Application\Process;

use Exception;
use RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

abstract class AbstractProcess
{
    use EntityManagerAwareTrait;

    protected function beginTransaction(): void
    {
        try {
            // Cf. https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/transactions.html
            $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
            $this->entityManager->getConnection()->beginTransaction();
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new RuntimeException("Echec de l'ouverture d'une transaction en bdd", 0, $e);
        }
    }

    protected function commit(): void
    {
        try {
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $exception = new RuntimeException("Echec du commit en bdd, rollback.", 0, $e);
            $this->rollback($exception);
            throw $exception;
        }
    }

    protected function rollback(?Exception $reason = null): void
    {
        try {
            $this->entityManager->getConnection()->rollBack();
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new RuntimeException(
                "Echec du rollback en bdd" . ($reason ? sprintf(" (raison du rollback : %s)", $reason->getMessage()) : ""),
                null,
                $e
            );
        }
    }

}