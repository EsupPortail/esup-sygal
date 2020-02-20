<?php

namespace ComiteSuivi\Service\CompteRendu;

trait CompteRenduServiceAwareTrait {

    /** @var CompteRenduService */
    private $compteRenduService;

    /**
     * @return CompteRenduService
     */
    public function getCompteRenduService()
    {
        return $this->compteRenduService;
    }

    /**
     * @param CompteRenduService $compteRenduService
     * @return CompteRenduService
     */
    public function setCompteRenduService($compteRenduService)
    {
        $this->compteRenduService = $compteRenduService;
        return $this->compteRenduService;
    }

}