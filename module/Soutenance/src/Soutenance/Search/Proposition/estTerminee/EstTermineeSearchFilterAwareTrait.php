<?php

namespace Soutenance\Search\Proposition\estTerminee;

trait EstTermineeSearchFilterAwareTrait
{
    protected ?EstTermineeSearchFilter $estTermineeSearchFilter = null;

    public function getEstTermineeSearchFilter(string $entityAlias = 'these', string $stateField = 'etatThese', array $etats = []): EstTermineeSearchFilter
    {
        if ($this->estTermineeSearchFilter === null) {
            $this->estTermineeSearchFilter = new EstTermineeSearchFilter($entityAlias, $stateField, $etats);
        }
        return $this->estTermineeSearchFilter;
    }

    public function setEstTermineeSearchFilter(EstTermineeSearchFilter $estTermineeSearchFilter): void
    {
        $this->estTermineeSearchFilter = $estTermineeSearchFilter;
    }
}