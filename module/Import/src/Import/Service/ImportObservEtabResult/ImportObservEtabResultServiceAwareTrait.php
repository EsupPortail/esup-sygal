<?php

namespace Import\Service\ImportObservEtabResult;

/**
 * @author Unicaen
 */
trait ImportObservEtabResultServiceAwareTrait
{
    /**
     * @var ImportObservEtabResultService
     */
    protected $importObservEtabResultService;

    /**
     * @param ImportObservEtabResultService $importObservEtabResultService
     * @return void
     */
    public function setImportObservEtabResultService(ImportObservEtabResultService $importObservEtabResultService)
    {
        $this->importObservEtabResultService = $importObservEtabResultService;
    }
}