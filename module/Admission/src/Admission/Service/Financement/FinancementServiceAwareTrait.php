<?php

namespace Admission\Service\Financement;

trait FinancementServiceAwareTrait
{
    /**
     * @var FinancementService
     */
    protected FinancementService $financementService;

    /**
     * @param FinancementService $financementService
     */
    public function setFinancementService(FinancementService $financementService): void
    {
        $this->financementService = $financementService;
    }

    /**
     * @return FinancementService
     */
    public function getFinancementService(): FinancementService
    {
        return $this->financementService;
    }
}