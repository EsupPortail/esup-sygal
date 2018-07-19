<?php

namespace Import\Service;

use Doctrine\DBAL\DBALException;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 *
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

        try {
            $this->entityManager->getConnection()->executeQuery($plsql);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur rrencontrée lors de l'exécution des procédures de synchro (import)", null, $e);
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

        $config = [
            'structure'       => [
                "UNICAEN_IMPORT.MAJ_STRUCTURE($sqlFilterSnippet);",
            ],
            'etablissement'   => [
                "UNICAEN_IMPORT.MAJ_ETABLISSEMENT($sqlFilterSnippet);",
            ],
            'ecole-doctorale' => [
                "UNICAEN_IMPORT.MAJ_ECOLE_DOCT($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'unite-recherche' => [
                "UNICAEN_IMPORT.MAJ_UNITE_RECH($sqlFilterSnippet);",
            ],
            'individu'        => [
                "UNICAEN_IMPORT.MAJ_INDIVIDU($sqlFilterSnippet);",
            ],
            'doctorant'       => [
                "UNICAEN_IMPORT.MAJ_DOCTORANT($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'these'           => [
                "UNICAEN_IMPORT.MAJ_THESE($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'role'            => [
                "UNICAEN_IMPORT.MAJ_ROLE($sqlFilterSnippet);",
            ],
            'acteur'          => [
                "UNICAEN_IMPORT.MAJ_ACTEUR($sqlFilterSnippet);",
                $this->backgroundify("APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');"),
            ],
            'variable'        => [
                "UNICAEN_IMPORT.MAJ_VARIABLE($sqlFilterSnippet);",
            ],
        ];

        if (!isset($config[$serviceName])) {
            throw new LogicException("Service spécifié inattendu: $serviceName");
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
}