<?php

namespace RapportActivite\Service\Operation;

trait RapportActiviteOperationServiceAwareTrait
{
    /**
     * @var RapportActiviteOperationService
     */
    protected RapportActiviteOperationService $rapportActiviteOperationService;

    /**
     * @param RapportActiviteOperationService $rapportActiviteOperationService
     */
    public function setRapportActiviteOperationService(RapportActiviteOperationService $rapportActiviteOperationService)
    {
        $this->rapportActiviteOperationService = $rapportActiviteOperationService;
    }
}