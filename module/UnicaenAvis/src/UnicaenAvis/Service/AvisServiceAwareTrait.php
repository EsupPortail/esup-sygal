<?php

namespace UnicaenAvis\Service;

trait AvisServiceAwareTrait
{
    protected AvisService $avisService;

    /**
     * @param \UnicaenAvis\Service\AvisService $avisService
     */
    public function setAvisService(AvisService $avisService)
    {
        $this->avisService = $avisService;
    }
}