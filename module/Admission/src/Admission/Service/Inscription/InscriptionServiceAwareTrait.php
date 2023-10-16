<?php

namespace Admission\Service\Inscription;

trait InscriptionServiceAwareTrait
{
    /**
     * @var InscriptionService
     */
    protected $inscriptionService;

    /**
     * @param InscriptionService $inscriptionService
     */
    public function setInscriptionService(InscriptionService $inscriptionService): void
    {
        $this->inscriptionService = $inscriptionService;
    }

    /**
     * @return InscriptionService
     */
    public function getInscriptionService(): InscriptionService
    {
        return $this->inscriptionService;
    }
}