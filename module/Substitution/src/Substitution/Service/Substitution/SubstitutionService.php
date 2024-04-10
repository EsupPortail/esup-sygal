<?php

namespace Substitution\Service\Substitution;

use Doctrine\DBAL\Result;
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
        foreach ($services as $service) {
            Assert::implementsInterface($service, SpecificSubstitutionServiceInterface::class);
            $this->specificServices[$service->getType()] = $service;
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function createSubstitutionForTypeAndSubstituable(string $type, string $substituableId, string $npd): Result
    {
        return $this->specificServices[$type]->createSubstitution($substituableId, $npd);
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
    public function findOneSubstitutionByTypeAndSubstituant(string $type, int $substituantId): Result
    {
        return $this->specificServices[$type]->findOneSubstitutionBySubstituant($substituantId);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOneSubstitutionByTypeAndSubstitue(string $type, int $substitueId): Result
    {
        return $this->specificServices[$type]->findOneSubstitutionBySubstitue($substitueId);
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

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function computeEntityNpd(string $type, int $substituableId): string
    {
        return $this->specificServices[$type]->computeEntityNpd($substituableId);
    }

    public function computeEntityNpdAttributesForType(string $type): array
    {
        return $this->specificServices[$type]->getEntityNpdAttributes();
    }
}