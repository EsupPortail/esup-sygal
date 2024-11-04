<?php

namespace Application\Service\AutorisationInscription;

trait AutorisationInscriptionServiceAwareTrait
{
    /**
     * @var AutorisationInscriptionService
     */
    protected $autorisationInscriptionService;

    /**
     * @param AutorisationInscriptionService $autorisationInscriptionService
     */
    public function setAutorisationInscriptionService(AutorisationInscriptionService $autorisationInscriptionService)
    {
        $this->autorisationInscriptionService = $autorisationInscriptionService;
    }

    public function getAutorisationInscriptionService(): AutorisationInscriptionService
    {
        return $this->autorisationInscriptionService;
    }
}