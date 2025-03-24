<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Process;

trait InscriptionAdministrativeImportProcessAwareTrait
{
    protected InscriptionAdministrativeImportProcess $inscriptionAdministrativeImportProcess;

    public function setInscriptionAdministrativeImportProcess(InscriptionAdministrativeImportProcess $importProcess): void
    {
        $this->inscriptionAdministrativeImportProcess = $importProcess;
    }
}