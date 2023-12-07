<?php

namespace Substitution\Service\Doublon;

use Doctrine\DBAL\Result;

interface SpecificDoublonServiceInterface
{
    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublons(): int;

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublons(int $limit = 100): Result;
}