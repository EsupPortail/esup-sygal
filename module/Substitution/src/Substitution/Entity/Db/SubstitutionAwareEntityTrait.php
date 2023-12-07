<?php

namespace Substitution\Entity\Db;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Webmozart\Assert\Assert;

trait SubstitutionAwareEntityTrait
{
    protected Collection $substitues;
    protected ?string $npdForce = null;
    protected bool $estSubstituantModifiable = false;

    public function getSubstitues(): Collection
    {
        return $this->substitues;
    }

    public function getNpdForce(): ?string
    {
        return $this->npdForce;
    }

    public function setNpdForce(?string $npdForce): void
    {
        $this->npdForce = $npdForce;
    }

    public function estSubstituant(): bool
    {
        return !$this->substitues->isEmpty();
    }

    public function estSubstituantModifiable(): bool
    {
        return $this->estSubstituantModifiable;
    }

    public function setEstSubstituantModifiable(bool $value = true): void
    {
        Assert::true($this->estSubstituant(), "L'enregistrement cible doit être un substituant");
        $this->estSubstituantModifiable = $value;
    }

    public function extractAttributeValues(array $attributes): array
    {
        $attributeToGetterFilter = new UnderscoreToCamelCase();
        $result = [];
        foreach ($attributes as $name => $label) {
            $getter = 'get' . ucfirst($attributeToGetterFilter->filter($name));
            $value = $this->$getter() ?? '(Non renseigné·e)';
            if ($value instanceof DateTime) {
                $value = $value->format('d/m/Y');
            }
            $result[$label] = $value;
        }

        return $result;
    }
}