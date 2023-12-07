<?php

namespace Substitution\Service\Doublon;

use Doctrine\DBAL\Result;
use UnicaenApp\Service\EntityManagerAwareTrait;

abstract class SpecificDoublonAbstractService implements SpecificDoublonServiceInterface
{
    use EntityManagerAwareTrait;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublons(): int
    {
        return $this->entityManager->getConnection()->executeQuery(
            'select count(*) nb from (' . $this->generateSqlToFindAllDoublons() . ') tmp'
        )->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublons(int $limit = 100): Result
    {
        return $this->entityManager->getConnection()->executeQuery(
            $this->generateSqlToFindAllDoublons() . " limit $limit"
        );
    }

    /**
     * Génère le SQL permettant de sélectionner tous les doublons.
     */
    abstract protected function generateSqlToFindAllDoublons(): string;
}