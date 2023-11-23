<?php

namespace Substitution\Entity\Db;

use Doctrine\Common\Collections\Collection;

trait SubstitutionAwareTrait
{
    protected Collection $substitues;

    protected bool $estSubstituantModifiable = false;

    public function getSubstitues(): Collection
    {
        return $this->substitues;
    }

    public function estSubstituant(): bool
    {
        return !$this->substitues->isEmpty();
    }

    public function estSubstituantModifiable(): bool
    {
        return $this->estSubstituantModifiable;
    }
}