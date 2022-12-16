<?php

namespace SygalApiImpl\V1\Facade;

use Doctrine\DBAL\Connection;
use stdClass;
use UnicaenDbImport\Domain\Import;
use UnicaenDbImport\Domain\Synchro;
use UnicaenDbImport\Service\ImportService;
use UnicaenDbImport\Service\SynchroService;

abstract class AbstractImportFacade
{
    protected Connection $destinationConnection;
    protected ImportService $importService;
    protected SynchroService $synchroService;

    public function setImportService(ImportService $importService): void
    {
        $this->importService = $importService;
    }

    public function setSynchroService(SynchroService $synchroService): void
    {
        $this->synchroService = $synchroService;
    }

    public function setDestinationConnection(Connection $destinationConnection): void
    {
        $this->destinationConnection = $destinationConnection;
    }

    /**
     * @throws \Exception
     */
    abstract public function import(stdClass $data);

    /**
     * @throws \Exception
     */
    protected function runImport(Import $import)
    {
        $result = $this->importService->runImport($import);

        if ($exception = $result->getFailureException()) {
            throw $exception;
        }
    }

    /**
     * @throws \Exception
     */
    protected function runSynchro(Synchro $import)
    {
        $result = $this->synchroService->runSynchro($import);

        if ($exception = $result->getFailureException()) {
            throw $exception;
        }
    }
}