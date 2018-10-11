<?php

namespace Import\Service\ImportObservResult;

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
     * @param ImportObservResultService $serviceImportObservResult
     * @return void
     */
    public function setImportObservResultService(ImportObservResultService $serviceImportObservResult)
    {
        $this->importObservResultService = $serviceImportObservResult;
    }
}