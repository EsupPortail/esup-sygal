<?php

namespace Application\Service\Rapport\Avis;

trait RapportAvisServiceAwareTrait
{
    /**
     * @var RapportAvisService
     */
    protected $rapportAvisService;

    /**
     * @param RapportAvisService $rapportAvisService
     */
    public function setRapportAvisService(RapportAvisService $rapportAvisService)
    {
        $this->rapportAvisService = $rapportAvisService;
    }
}