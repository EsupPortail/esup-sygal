<?php

namespace Formation\Service\Inscription;

trait InscriptionServiceAwareTrait
{
    /** @var InscriptionService */
    private $seanceService;

    /**
     * @return InscriptionService
     */
    public function getInscriptionService(): InscriptionService
    {
        return $this->seanceService;
    }

    /**
     * @param InscriptionService $seanceService
     * @return InscriptionService
     */
    public function setInscriptionService(InscriptionService $seanceService): InscriptionService
    {
        $this->seanceService = $seanceService;
        return $this->seanceService;
    }

}