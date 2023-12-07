<?php

namespace Substitution\Service\Substitution;

use Doctrine\DBAL\Result;
use Substitution\Constants;
use Webmozart\Assert\Assert;

class SubstitutionService
{
    /** @var SpecificSubstitutionServiceInterface[] */
    protected array $specificServices;

    /**
     * @param SpecificSubstitutionServiceInterface[] $services
     */
    public function setSpecificSubstitutionServices(array $services): void
    {
        Assert::allInArray(array_keys($services), Constants::TYPES, 'La clé %s ne fait pas partie des valeurs autorisées : %2$s');
        Assert::allImplementsInterface($services, SpecificSubstitutionServiceInterface::class);

        $this->specificServices = $services;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function countAllSubstitutionsForType(string $type): int
    {
        return $this->specificServices[$type]->countAllSubstitutions();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findAllSubstitutionsForType(string $type, ?int $limit = null): Result
    {
        return $this->specificServices[$type]->findAllSubstitutions($limit);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionForType(string $type, int $substituantId): Result
    {
        return $this->specificServices[$type]->findOneSubstitution($substituantId);
    }

    public function findSubstituableForTypeByText(string $type, string $text, string $npd): array
    {
        return $this->specificServices[$type]->findSubstituablesByText($text, $npd);
    }

    public function addSubstitueToSubstitutionForType(string $type, int $substitueId, string $npd): void
    {
        $this->specificServices[$type]->addSubstitueToSubstitution($substitueId, $npd);
    }

    public function removeSubstitueFromSubstitutionForType(string $type, int $substitueId, string $npd): void
    {
        $this->specificServices[$type]->removeSubstitueFromSubstitution($substitueId, $npd);
    }

    public function findOneEntityByTypeAndId(string $type, int $id): object
    {
        return $this->specificServices[$type]->findOneEntityById($id);
    }

    public function updateSubstituantByTypeAndId(string $type, int $substituantId, array $data): void
    {
        Assert::keyExists($data, 'estSubstituantModifiable');
        Assert::boolean($data['estSubstituantModifiable']);

        $entity = $this->findOneEntityByTypeAndId($type, $substituantId);
        $entity->setEstSubstituantModifiable($data['estSubstituantModifiable']);

        $this->specificServices[$type]->saveEntity($entity);
    }

    public function computeEntityNpdAttributesForType(string $type): array
    {
        return $this->specificServices[$type]->getEntityNpdAttributes();
    }
}