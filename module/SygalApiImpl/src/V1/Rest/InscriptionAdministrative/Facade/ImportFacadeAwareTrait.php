<?php

namespace SygalApiImpl\V1\Rest\InscriptionAdministrative\Facade;

trait ImportFacadeAwareTrait
{
    protected ImportFacade $importFacade;

    public function setImportFacade(ImportFacade $importFacade): void
    {
        $this->importFacade = $importFacade;
    }
}