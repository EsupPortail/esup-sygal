<?php

namespace Admission\Service\Financement;

trait FinancementServiceAwareTrait
{
    /**
     * @var FinancementService
     */
    protected FinancementService $admissionFinancementService;

    /**
     * @param FinancementService $admissionFinancementService
     */
    public function setFinancementService(FinancementService $admissionFinancementService): void
    {
        $this->admissionFinancementService = $admissionFinancementService;
    }

    /**
     * @return FinancementService
     */
    public function getFinancementService(): FinancementService
    {
        return $this->admissionFinancementService;
    }
}