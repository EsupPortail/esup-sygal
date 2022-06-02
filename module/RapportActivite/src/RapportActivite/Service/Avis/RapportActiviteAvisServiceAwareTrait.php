<?php

namespace RapportActivite\Service\Avis;

trait RapportActiviteAvisServiceAwareTrait
{
    /**
     * @var RapportActiviteAvisService
     */
    protected RapportActiviteAvisService $rapportActiviteAvisService;

    /**
     * @param RapportActiviteAvisService $rapportActiviteAvisService
     */
    public function setRapportActiviteAvisService(RapportActiviteAvisService $rapportActiviteAvisService)
    {
        $this->rapportActiviteAvisService = $rapportActiviteAvisService;
    }
}