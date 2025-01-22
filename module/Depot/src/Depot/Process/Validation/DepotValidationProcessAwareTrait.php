<?php

namespace Depot\Process\Validation;

trait DepotValidationProcessAwareTrait
{
    protected DepotValidationProcess $depotValidationProcess;

    public function setDepotValidationProcess(DepotValidationProcess $depotValidationProcess): void
    {
        $this->depotValidationProcess = $depotValidationProcess;
    }
}