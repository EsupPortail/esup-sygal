<?php

namespace SygalApiImpl\V1\Facade;

use Doctrine\DBAL\Connection;
use Exception;
use RuntimeException;
use stdClass;
use UnicaenDbImport\Domain\Import;
use UnicaenDbImport\Domain\ImportResult;
use UnicaenDbImport\Domain\Synchro;
use UnicaenDbImport\Domain\SynchroResult;
use UnicaenDbImport\Service\Exception\DatabaseServiceException;
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
    protected function runImport(Import $import): ImportResult
    {
        try {
            return $this->importService->runImport($import);
        } catch (DatabaseServiceException $e) {
            throw new Exception("Une erreur est survenue lors de l'import : " . $e->getMessage(), null, $e);
        }
    }

    /**
     * @throws \Exception
     */
    protected function runSynchro(Synchro $synchro): SynchroResult
    {
        try {
            return $this->synchroService->runSynchro($synchro);
        } catch (DatabaseServiceException $e) {
            throw new Exception("Une erreur est survenue lors de la synchro : " . $e->getMessage(), null, $e);
        }
    }

    protected function beginTransaction(): void
    {
        try {
            $this->destinationConnection->setNestTransactionsWithSavepoints(true); // transactions imbriquées
            $this->destinationConnection->beginTransaction();
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new RuntimeException("Echec de l'ouverture d'une transaction en bdd", null, $e);
        }
    }

    protected function commit()
    {
        try {
            $this->destinationConnection->commit();
        } catch (\Doctrine\DBAL\Exception $e) {
            $exception = new RuntimeException("Echec du commit en bdd, rollback.", null, $e);
            $this->rollback($exception);
        }
    }

    protected function rollback(Exception $reason)
    {
        try {
            $this->destinationConnection->rollback();
        } catch (\Doctrine\DBAL\Exception $e) {
            throw new RuntimeException(
                sprintf("Echec du rollback en bdd (raison du rollback : %s)", $reason->getMessage()), null, $e
            );
        }
    }
}