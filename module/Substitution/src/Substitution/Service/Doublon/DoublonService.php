<?php

namespace Substitution\Service\Doublon;

use Doctrine\DBAL\Result;
use Substitution\Constants;
use Webmozart\Assert\Assert;

class DoublonService
{
    /** @var \Substitution\Service\Doublon\SpecificDoublonServiceInterface[] */
    protected array $specificServices;

    /**
     * @param \Substitution\Service\Doublon\SpecificDoublonServiceInterface[] $services
     */
    public function setSpecificServices(array $services): void
    {
        Assert::allInArray(array_keys($services), Constants::TYPES, 'La clé %s ne fait pas partie des valeurs autorisées : %2$s');
        Assert::allImplementsInterface($services, SpecificDoublonServiceInterface::class);

        $this->specificServices = $services;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllDoublonsForType(string $type): int
    {
        return $this->specificServices[$type]->countAllDoublons();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllDoublonsForType(string $type, int $limit = 100): Result
    {
        return $this->specificServices[$type]->findAllDoublons($limit);
    }




}