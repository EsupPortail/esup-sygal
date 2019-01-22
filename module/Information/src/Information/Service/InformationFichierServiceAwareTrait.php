<?php

namespace Information\Service;

trait InformationFichierServiceAwareTrait {

    /** @var InformationFichierService */
    private $informationFichierService;

    /**
     * @return InformationFichierService
     */
    public function getInformationFichierService()
    {
        return $this->informationFichierService;
    }

    /**
     * @param InformationFichierService $informationFichierService
     * @return InformationFichierService
     */
    public function setInformationFichierService($informationFichierService)
    {
        $this->informationFichierService = $informationFichierService;
        return $this->informationFichierService;
    }


}