<?php

namespace InscriptionAdministrative\Process\Reception;

trait InscriptionAdministrativeReceptionProcessAwareTrait
{
    protected InscriptionAdministrativeReceptionProcess $inscriptionAdministrativeReceptionProcess;

    public function setInscriptionAdministrativeReceptionProcess(InscriptionAdministrativeReceptionProcess $inscriptionAdministrativeReceptionProcess): void
    {
        $this->inscriptionAdministrativeReceptionProcess = $inscriptionAdministrativeReceptionProcess;
    }
}