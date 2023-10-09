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
    public function setInscriptionService(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }

    /**
     * @return InscriptionService
     */
    public function getInscriptionService()
    {
        return $this->inscriptionService;
    }
}