<?php

namespace Substitution\Service;

trait SubstitutionServiceAwareTrait
{
    protected SubstitutionService $substitutionService;

    public function getSubstitutionService(): SubstitutionService
    {
        return $this->substitutionService;
    }

    public function setSubstitutionService(SubstitutionService $substitutionService): void
    {
        $this->substitutionService = $substitutionService;
    }
}