<?php

namespace Application\Service\Information;

trait InformationServiceAwareTrait {

    /** @var InformationService */
    private $informationService;

    /**
     * @return InformationService
     */
    public function getInformationService()
    {
        return $this->informationService;
    }

    /**
     * @param InformationService $informationService
     * @return InformationService
     */
    public function setInformationService($informationService)
    {
        $this->informationService = $informationService;
        return $this->informationService;
    }
}