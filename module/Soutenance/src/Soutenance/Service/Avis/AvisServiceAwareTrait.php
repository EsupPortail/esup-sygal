<?php

namespace  Soutenance\Service\Avis;

trait AvisServiceAwareTrait {

    /** @var AvisService */
    protected $avisService;

    /**
     * @return AvisService
     */
    public function getAvisService()
    {
        return $this->avisService;
    }

    /**
     * @param AvisService $avisService
     * @return AvisService
     */
    public function setAvisService($avisService)
    {
        $this->avisService = $avisService;
        return $this->avisService;
    }

}