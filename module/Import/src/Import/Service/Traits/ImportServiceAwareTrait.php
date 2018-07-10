<?php

namespace Import\Service\Traits;

use Import\Service\ImportService;

trait ImportServiceAwareTrait
{
    /**
     * @var ImportService
     */
    protected $importService;

    /**
     * @param ImportService $importService
     * @return self
     */
    public function setImportService(ImportService $importService)
    {
        $this->importService = $importService;

        return $this;
    }
}