<?php

namespace Import\Service;

use DateTime;
use Doctrine\DBAL\ConnectionException;
use Exception;
use Import\Model\SynchroLog;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use UnicaenDbImport\Service\Traits\SynchroServiceAwareTrait as DbImportSynchroServiceAwareTrait;

/**
 * Service chargé de réaliser la synchro UnicaenImport en base de données.
 *
 * @author Unicaen
 */
class SynchroService
{
    use EntityManagerAwareTrait;
    use DbImportSynchroServiceAwareTrait;

    private $services = [];

    /**
     * @param string $service Nom du service
     * @param array $params Ex: ['sql_filter' => "SOURCE_CODE = ''UCN::21311123''"]
     * @return self
     */
    public function addService(string $service, array $params = []): self
    {
        $this->services[$service] = $params;

        return $this;
    }

    /**
     * Lance la synchro des données par UnicaenImport pour tous les services inscrits.
     *
     * @throws \Doctrine\DBAL\Exception Erreur imprévue en base de données
     */
    public function synchronize()
    {
        if (count($this->services) === 0) {
            throw new LogicException("Aucun service à synchroniser");
        }

        foreach ($this->services as $name => $params) {
            $synchro = $this->synchroService->getSynchroByName($name);
            $result = $this->synchroService->runSynchro($synchro);
            if ($exception = $result->getFailureException()) {
                throw new RuntimeException(
                    sprintf("Erreur rencontrée pendant la synchro '%s'", $synchro->getName()),
                    null,
                    $result->getFailureException());
            }
        }
        $this->executeProcedureCalls([
            'refresh materialized view mv_recherche_these;',
        ]);
    }

    /**
     * @param string[] $calls
     */
    private function executeProcedureCalls(array $calls)
    {
        $plsql = implode(PHP_EOL, array_merge(['DO $$ BEGIN'], $calls, ['END $$;']));

        $startDate = date_create();

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeQuery($plsql);
            $connection->commit();
            $success = true;
            $message = null;
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur rencontrée lors de l'exécution des procédures de synchro (import) : " . PHP_EOL . $e->getMessage();
            try {
                $connection->rollBack();
            } catch (ConnectionException $e) {
                $message .= PHP_EOL . "Et en plus le rollback a échoué!";
            }
        } finally {
            $finishDate = date_create();
            $status = $success ? 'SUCCESS' : 'FAILURE';
            $this->log($startDate, $finishDate, $plsql, $status, $message);
        }

        if (! $success) {
            throw new RuntimeException($message);
        }
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $finishDate
     * @param string $sql
     * @param string $status
     * @param null $message
     */
    private function log(DateTime $startDate, DateTime $finishDate, string $sql, string $status, $message = null)
    {
        $log = (new SynchroLog())
            ->setLogDate(date_create())
            ->setStartDate($startDate)
            ->setFinishDate($finishDate)
            ->setSql($sql)
            ->setStatus($status)
            ->setMessage($message);

        try {
            $this->entityManager->persist($log);
            $this->entityManager->flush($log);
        } catch (Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du SynchroLog.", null, $e);
        }
    }
}