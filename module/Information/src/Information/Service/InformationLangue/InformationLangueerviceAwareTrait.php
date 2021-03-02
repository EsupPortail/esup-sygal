<?php

namespace Information\Service\InformationLangue;

trait InformationLangueerviceAwareTrait {

    /** @var InformationLangueService */
    private $informationLangueService;

    /**
     * @return InformationLangueService
     */
    public function getInformationLangueService(): InformationLangueService
    {
        return $this->informationLangueService;
    }

    /**
     * @param InformationLangueService $informationLangueService
     * @return InformationLangueService
     */
    public function setInformationLangueService(InformationLangueService $informationLangueService): InformationLangueService
    {
        $this->informationLangueService = $informationLangueService;
        return $this->informationLangueService;
    }


}