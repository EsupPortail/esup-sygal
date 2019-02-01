<?php

namespace Import\Service;

use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Import\Model\SynchroLog;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Filter\Word\DashToUnderscore;

/**
 * Service chargé de réaliser la synchro UnicaenImport en base de données.
 *
 * @author Unicaen
 */
class SynchroService
{
    use EntityManagerAwareTrait;

    private $services = [];

    /**
     * @param string $service Nom du service
     * @param array  $params Ex: ['sql_filter' => "SOURCE_CODE = ''UCN::21311123''"]
     * @return self
     */
    public function addService($service, array $params = [])
    {
        $this->services[$service] = $params;

        return $this;
    }

    /**
     * @return self
     */
    public function removeAllServices()
    {
        $this->services = [];

        return $this;
    }

    /**
     * Lance la synchro des données par UnicaenImport pour tous les services inscrits.
     */
    public function synchronize()
    {
        if (count($this->services) === 0) {
            throw new LogicException("Aucun service à synchroniser");
        }

        // détermination des appels de procédures de synchro à faire
        $calls = [];
        foreach ($this->services as $service => $params) {
            $sqlFilter = isset($params['sql_filter']) ? $params['sql_filter'] : [];
            $calls = array_merge($calls, $this->getImportProcedureCallsForService($service, $sqlFilter));
        }
        // suppression des appels en double EN CONSERVANT LE DERNIER appel et non le premier
        $calls = array_reverse(array_unique(array_reverse($calls)));

        $this->executeProcedureCalls($calls);
    }

    /**
     * @param string[] $calls
     */
    private function executeProcedureCalls(array $calls)
    {
        $plsql = implode(PHP_EOL, array_merge(['BEGIN'], $calls, ['END;']));

        $startDate = date_create();

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeQuery($plsql);
            $connection->commit();
            $success = true;
            $message = null;
        } catch (DBALException $e) {
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
     * Retourne, pour un service donné, les appels ORDONNÉS de procédures de synchronisation à lancer.
     *
     * @param string $serviceName
     * @param string $sqlFilter
     * @return string[]
     */
    private function getImportProcedureCallsForService($serviceName, $sqlFilter = null)
    {
        $sqlFilterSnippet = $sqlFilter ? "'WHERE " . str_replace("'", "''", $sqlFilter) . "'" : '';

        // config de base pour les services où :
        // - il n'y a qu'une procédure à appeler, et
        // - le nom de cette procédure peut être déduite du nom du service.
        $defaultConfig = [];
        $f = new DashToUnderscore();
        foreach (ImportService::SERVICES as $service) {
            $procName = 'MAJ_' . strtoupper($f->filter($service)); // ex: 'titre-acces' => 'MAJ_TITRE_ACCES'
            $defaultConfig[$service] = [
                "UNICAEN_IMPORT.$procName($sqlFilterSnippet);",
            ];
        }

        // modif de la config de base pour certains services
        $config = array_merge($defaultConfig, [
            'ecole-doctorale' => [
                "UNICAEN_IMPORT.MAJ_ECOLE_DOCT($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'unite-recherche' => [
                "UNICAEN_IMPORT.MAJ_UNITE_RECH($sqlFilterSnippet);",
            ],
            'doctorant'       => [
                "UNICAEN_IMPORT.MAJ_DOCTORANT($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'these'           => [
                "UNICAEN_IMPORT.MAJ_THESE($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'acteur'          => [
                "UNICAEN_IMPORT.MAJ_ACTEUR($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
        ]);

        if (!array_key_exists($serviceName, $config)) {
            throw new LogicException("Service spécifié inattendu : '$serviceName'.'");
        }

        return $config[$serviceName];
    }

    /**
     * Génère le code PL/SQL permettant de lancer du PL/SQL en tâche de fond.
     *
     * @param string $plsql
     * @param int    $secondsToWait
     * @return string
     */
    private function backgroundify($plsql, $secondsToWait = 0)
    {
        $nextDateSql = 'SYSDATE';
        if ($secondsToWait > 0) {
            $nextDateSql = sprintf("SYSDATE + INTERVAL '%d' SECOND", $secondsToWait);
        }

        $template = <<<EOS
DECLARE
    L_jobno INTEGER;
BEGIN
    DBMS_JOB.SUBMIT(L_jobno, 'BEGIN %s END;', $nextDateSql);
    COMMIT;
END;
EOS;

        return sprintf($template, str_replace("'", "''", $plsql));
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $finishDate
     * @param string    $sql
     * @param string    $status
     * @param string    $message
     */
    private function log(DateTime $startDate, DateTime $finishDate, $sql, $status, $message = null)
    {
        $log = (new SynchroLog())
            ->setLogDate(date_create())
            ->setStartDate($startDate)
            ->setFinishDate($finishDate)
            ->setSql($sql)
            ->setStatus($status)
            ->setMessage($message);

        $this->entityManager->persist($log);
        try {
            $this->entityManager->flush($log);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement du SynchroLog.", null, $e);
        }
    }
}