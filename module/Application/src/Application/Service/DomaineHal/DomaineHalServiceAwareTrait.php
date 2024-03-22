<?php

namespace Application\Service\DomaineHal;

trait DomaineHalServiceAwareTrait {

    private DomaineHalService $domaineHalService;

    /**
     * @return DomaineHalService
     */
    public function getDomaineHalService(): DomaineHalService
    {
        return $this->domaineHalService;
    }

    /**
     * @param DomaineHalService $domaineHalService
     */
    public function setDomaineHalService(DomaineHalService $domaineHalService): void
    {
        $this->domaineHalService = $domaineHalService;
    }


}