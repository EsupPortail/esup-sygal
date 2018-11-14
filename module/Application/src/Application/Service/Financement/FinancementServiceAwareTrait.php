<?php

namespace Application\Service\Financement;

trait FinancementServiceAwareTrait {

    /** @var FinancementService */
    private $financementService;

    /**
     * @return FinancementService
     */
    public function getFinancementService()
    {
        return $this->financementService;
    }

    /**
     * @param FinancementService $financementService
     * @return FinancementService
     */
    public function setFinancementService($financementService)
    {
        $this->financementService = $financementService;
        return $this->financementService;
    }



}