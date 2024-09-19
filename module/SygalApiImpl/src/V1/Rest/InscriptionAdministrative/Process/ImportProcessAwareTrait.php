<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Process;

trait ImportProcessAwareTrait
{
    protected ImportProcess $importFacade;

    public function setImportFacade(ImportProcess $importFacade): void
    {
        $this->importFacade = $importFacade;
    }
}