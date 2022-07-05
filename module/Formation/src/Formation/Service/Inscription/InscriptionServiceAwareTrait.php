<?php

namespace Formation\Service\Inscription;

trait InscriptionServiceAwareTrait
{
    private InscriptionService $inscriptionService;

    /**
     * @return InscriptionService
     */
    public function getInscriptionService(): InscriptionService
    {
        return $this->inscriptionService;
    }

    /**
     * @param InscriptionService $inscriptionService
     * @return InscriptionService
     */
    public function setInscriptionService(InscriptionService $inscriptionService): InscriptionService
    {
        $this->inscriptionService = $inscriptionService;
        return $this->inscriptionService;
    }

}