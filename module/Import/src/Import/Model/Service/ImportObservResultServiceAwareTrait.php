<?php

namespace Import\Model\Service;

/**
 * @author Unicaen
 */
trait ImportObservResultServiceAwareTrait
{
    /**
     * @var ImportObservResultService
     */
    protected $importObservResultService;

    /**
     * @param ImportObservResultService $importObservResultService
     */
    public function setImportObservResultService(ImportObservResultService $importObservResultService)
    {
        $this->importObservResultService = $importObservResultService;
    }
}