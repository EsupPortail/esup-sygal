<?php

namespace RapportActivite\Service\Fichier;

trait RapportActiviteFichierServiceAwareTrait
{
    protected RapportActiviteFichierService $rapportActiviteFichierService;

    /**
     * @param \RapportActivite\Service\Fichier\RapportActiviteFichierService $rapportActiviteFichierService
     */
    public function setRapportActiviteFichierService(RapportActiviteFichierService $rapportActiviteFichierService): void
    {
        $this->rapportActiviteFichierService = $rapportActiviteFichierService;
    }
}