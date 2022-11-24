<?php

namespace Depot\Service\Validation;

trait DepotValidationServiceAwareTrait
{
    /**
     * @var DepotValidationService
     */
    protected DepotValidationService $depotValidationService;

    /**
     * @param DepotValidationService $depotValidationService
     */
    public function setDepotValidationService(DepotValidationService $depotValidationService)
    {
        $this->depotValidationService = $depotValidationService;
    }
}