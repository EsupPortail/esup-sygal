<?php

namespace Substitution\Service\Substitution;

use Substitution\Service\Substitution\SubstitutionService;

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