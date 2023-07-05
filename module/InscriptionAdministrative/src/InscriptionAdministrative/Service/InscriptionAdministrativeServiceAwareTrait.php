<?php

namespace InscriptionAdministrative\Service;

trait InscriptionAdministrativeServiceAwareTrait
{
    protected InscriptionAdministrativeService $inscriptionAdministrativeService;

    public function setInscriptionAdministrativeService(InscriptionAdministrativeService $inscriptionAdministrativeService): void
    {
        $this->inscriptionAdministrativeService = $inscriptionAdministrativeService;
    }
}