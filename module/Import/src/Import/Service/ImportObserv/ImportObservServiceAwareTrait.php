<?php

namespace Import\Service\ImportObserv;

trait ImportObservServiceAwareTrait
{
    /**
     * @var ImportObservService
     */
    protected $importObservService;

    /**
     * @param ImportObservService $serviceImportObserv
     */
    public function setImportObservService(ImportObservService $serviceImportObserv)
    {
        $this->importObservService = $serviceImportObserv;
    }
}