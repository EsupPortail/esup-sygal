<?php

namespace ComiteSuivi\Service\CompteRendu;

trait CompteRenduServiceAwareTrait {

    /** @var CompteRenduService */
    private $compteRenduService;

    /**
     * @return CompteRenduService
     */
    public function getCompteRenduService() : CompteRenduService
    {
        return $this->compteRenduService;
    }

    /**
     * @param CompteRenduService $compteRenduService
     * @return CompteRenduService
     */
    public function setCompteRenduService(CompteRenduService $compteRenduService) : CompteRenduService
    {
        $this->compteRenduService = $compteRenduService;
        return $this->compteRenduService;
    }

}