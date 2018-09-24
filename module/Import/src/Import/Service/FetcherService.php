<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Filter\EtablissementPrefixFilter;
use Assert\Assertion;
use Assert\AssertionFailedException;
use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Import\Service\Traits\CallServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 * FetcherService est un service dédié à la récupération des données provenant du Web Service fourni par chaque
 * établissement.
 *
 * Chaque appel à ce service va insérer une ligne de log dans la table API_LOG.
 */
class FetcherService
{
    use CallServiceAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * @var array $config [ 'CODE_ETABLISSEMENT' => [...] ]
     */
    protected $config;

    /**
     * @var Etablissement
     */
    protected $etablissement;

    /**
     * @var array
     */
    protected $logs = [];

    /**
     * @var int
     */
    protected $debugLevel = 0;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     * @param array         $config
     */
    public function __construct(EntityManager $entityManager, $config)
    {
        $this->setEntityManager($entityManager);
        $this->config = $config;
    }

    /**
     * @param array $config
     * @return self
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @param int $debugLevel
     * @return self
     */
    public function setDebugLevel($debugLevel)
    {
        $this->debugLevel = $debugLevel;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function version()
    {
        $config = $this->getConfigForEtablissement();

        return $this->callService->setConfig($config)->version();
    }

    /**
     * Point d'entrée pour l'interrogation du web service d'import de données d'un établissement.
     *
     * @param string $serviceName
     * @param mixed  $sourceCode Source code avec ou sans préfixe
     * @param array  $filters
     */
    public function fetch($serviceName, $sourceCode = null, array $filters = [])
    {
        $sourceCode = $this->normalizeSourceCode($sourceCode);
        $entityClass = $this->entityNameForService($serviceName);

        if ($sourceCode !== null) {
            $this->fetchRow($serviceName, $entityClass, $sourceCode);
        } else {
            $this->fetchRows($serviceName, $entityClass, $filters);
        }
    }

    /**
     * @param string $serviceName
     * @param string $entityClass
     * @param string $sourceCode
     */
    private function fetchRow($serviceName, $entityClass, $sourceCode)
    {
        $this->logs = [];

        $debut = microtime(true);
        $start_date = new DateTime();

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($this->debugLevel > 1) {
            $this->logMetadata($serviceName, $entityClass);
        }

        /** Appel du web service */
        $_debut = microtime(true);
        $uri = $serviceName . "/" . $sourceCode;
        $this->callService->setConfig($this->getConfigForEtablissement());
        $json = $this->callService->get($uri);
        $_fin = microtime(true);
        if ($this->debugLevel > 0) {
            $this->log("WebService: " . ($_fin - $_debut) . " secondes.");
        }

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $_debut = microtime(true);
        /** Vidage des données de la table */
        $queries = [];
        $queries[] = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->etablissement->getCode() . "' AND ID='" . $sourceCode . "'";

        /** Remplissage avec les données retournées par le Web Services */
        $colonnes = implode(", ", $tableRelation);
        $values = implode(", ", $this->generateValueArray($json, $metadata));
        $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
        $queries[] = $query;
        $_fin = microtime(true);
        if ($this->debugLevel > 0) {
            $this->log("PrepQueries: " . ($_fin - $_debut) . " secondes.");
        }

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            if ($this->debugLevel > 1) {
                $this->log("Execution de la requête [" . $query . "]");
            }
            try {
                $connection->executeQuery($query);
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur lors de la mise à jour de la table $tableName en BDD", null, $e);
            }
        }
        try {
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->insertLog($start_date, new DateTime(), $uri, "ERROR_DB", $e->getMessage());
            return;
        }
        $_fin = microtime(true);
        if ($this->debugLevel > 0) {
            $this->log("ExecQueries: " . ($_fin - $_debut) . " secondes.");
        }

        $this->insertLog(
            $start_date, new DateTime(),
            $uri,
            "OK",
            "Récupération de " . $serviceName . ":" . $sourceCode . " de [" . $this->etablissement->getCode() . "] en " . ($_fin - $debut) . " seconde(s).",
            $this->etablissement->getCode(),
            "variable");
    }

    /**
     * @param string $service
     * @param string $entityClass
     * @param array  $filters
     */
    public function fetchRows($service, $entityClass, array $filters = [])
    {
        $this->logs = [];

        $debut = microtime(true);
        $start_date = new DateTime();

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($this->debugLevel > 1) {
            $this->logMetadata($service, $entityClass);
        }

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        /** Suppression des données existantes en BDD */
        $query = $this->generateSQLSnippetForDelete($tableName, $filters);
        try {
            $connection->executeQuery($query);
        } catch (DBALException $e) {
            $message = "Erreur lors du vidage de la table $tableName en BDD";
            try {
                $connection->rollBack();
            } catch (ConnectionException $e) {
                throw new RuntimeException($message . " et le rollback a échoué", null, $e);
            }
            throw new RuntimeException($message, null, $e);
        }

        /** Appels du web service */
        $this->callService->setConfig($this->getConfigForEtablissement());
        $entries = [];
        $filtersForWebService = $this->prepareFiltersForWebServiceRequest($filters);
        $page = 1;
        do {
            $params = array_merge($filtersForWebService, ['page' => $page]);
            $uri = $service;
            if (count($params) > 0) {
                $uri .= '?' . http_build_query($params);
            }
            $json = $this->callService->get($uri);

            $pageCount = $json->page_count; // NB: même valeur retournée à chaque requête (nombre total de pages)
            $jsonName = str_replace("-", "_", $service);
            $collection = $json->{'_embedded'}->{$jsonName};
            $entries = array_merge($entries, $collection);
            $page++;

            /** Construction des requêtes d'INSERTion **/
            $queries = [];
            foreach ($collection as $entry) {
                $colonnes = implode(", ", $tableRelation);
                $values = implode(", ", $this->generateValueArray($entry, $metadata));
                $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
                $queries[] = $query;
            }

            /** Execution des requetes d'INSERTion */
            $_debut = microtime(true);
            foreach ($queries as $query) {
                if ($this->debugLevel > 1) {
                    $this->log("Execution de la requête [" . $query . "]");
                }
                try {
                    $connection->executeQuery($query);
                } catch (DBALException $e) {
                    $message = "Erreur lors de la mise à jour de la table $tableName en BDD";
                    try {
                        $connection->rollBack();
                    } catch (ConnectionException $e) {
                        throw new RuntimeException($message . " et le rollback a échoué", null, $e);
                    }
                    throw new RuntimeException($message, null, $e);
                }
            }
            $_fin = microtime(true);
            if ($this->debugLevel > 0) {
                $this->log("Inserts: " . ($_fin - $_debut) . " secondes.");
            }
        }
        while ($page <= $pageCount);

        /** Commit **/
        $_debut = microtime(true);
        try {
            $this->entityManager->getConnection()->commit();
        } catch (ConnectionException $e) {
            throw new RuntimeException("Le commit a échoué", null, $e);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur inattendue", null, $e);
        }
        $_fin = microtime(true);
        if ($this->debugLevel > 0) {
            $this->log("Commit: " . ($_fin - $_debut) . " secondes.");
        }

        $fin = microtime(true);

        $this->insertLog(
            $start_date,
            new DateTime(),
            $uri, "OK", count($entries) . " " . $service . "(s) ont été récupéré(es) de [" . $this->etablissement->getCode() . "] en " . ($fin - $debut) . " seconde(s).",
            $this->etablissement->getCode(),
            $uri
        );
    }

    /**
     * @param string        $message
     * @param DateTime|null $date
     * @return self
     */
    private function log($message, \DateTime $date = null)
    {
        $this->logs[] = [
            'date'    => $date ?: new DateTime(),
            'message' => $message,
        ];

        return $this;
    }

    /**
     * @return array [[DateTime, string]] le log associé pour l'affichage
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param string service
     * @return string
     */
    private function entityNameForService($service)
    {
        $entityName = str_replace("-", " ", $service);
        $entityName = ucwords($entityName);
        $entityName = str_replace(" ", "", $entityName);
        $entityClass = "Import\Model\Tmp" . $entityName;

        return $entityClass;
    }

    /**
     * @param string $sourceCode
     * @return string
     */
    private function normalizeSourceCode($sourceCode)
    {
        if (! $sourceCode) {
            return $sourceCode;
        }

        $f = new EtablissementPrefixFilter();
        try {
            $sourceCode = $f->removePrefixFrom($sourceCode);
        } catch (LogicException $e) {
        }

        return $sourceCode;
    }

    /**
     * Retourne la config à transmettre au service d'appel du WS pour l'établissement courant.
     *
     * @return array
     */
    private function getConfigForEtablissement()
    {
        if ($this->etablissement === null) {
            throw new LogicException("Le code établissement courant est null.");
        }

        $codeEtablissement = $this->etablissement->getCode();

        try {
            Assertion::keyIsset($this->config, $codeEtablissement);
        } catch (AssertionFailedException $e) {
            throw new LogicException("Le code établissement '{$codeEtablissement}' est introuvable dans la config.", null, $e);
        }

        return $this->config[$codeEtablissement];
    }

    /**
     * Fonction de mise en forme des données typées pour les requêtes Oracle
     *
     * @param mixed  $value : la value à formater
     * @param string $type  : le type de la donnée à formater
     * @return string la donnée formatée
     *
     * RMQ si un format n'est pas prévu par le traitement la valeur est retournée sans traitement et un message est
     * affiché
     */
    protected function prepValue($value, $type)
    {
        $nvalue = null;
        switch ($type) {
            case "string":
                $nvalue = $this->prepString($value);
                break;
            case "date":
                $nvalue = $this->prepDate($value);
                break;
            default:
                $nvalue = $value;
                break;
        }

        return $nvalue;
    }

    protected function prepString($value)
    {
        return ("'" . str_replace("'", "''", $value) . "'");
    }

    protected function prepDate($value)
    {
        if ($value === null) {
            return "null";
        }
        $date = explode(' ', $value->{'date'})[0];

        return "to_date('" . $date . "','YYYY-MM-DD')";
    }

    /**
     * Fonction écrivant dans la table des logs API_LOGS
     *
     * @param DateTime $start_date : date de début du traitement
     * @param DateTime $end_date   : date de fin (avec succés ou échec) du traitement
     * @param string   $route      : la route du traitement
     * @param          $status
     * @param string   $response   : le message associé au traitement
     * @param string   $etablissement
     * @param string   $table
     */
    public function insertLog(DateTime $start_date, DateTime $end_date, $route, $status, $response, $etablissement = null, $table = null)
    {
        $log_query = "INSERT INTO API_LOG ";
        $log_query .= "(ID, REQ_URI, REQ_START_DATE, REQ_END_DATE, REQ_STATUS, REQ_RESPONSE, REQ_ETABLISSEMENT, REQ_TABLE)";
        $log_query .= " values (";
        $log_query .= "API_LOG_ID_SEQ.nextval ,";
        $log_query .= "'" . $route . "' , ";
        $log_query .= "to_date('" . $start_date->format('y-m-d H:i:s') . "','YY-MM-DD HH24:MI:SS') , ";
        $log_query .= "to_date('" . $end_date->format('y-m-d H:i:s') . "','YY-MM-DD HH24:MI:SS') , ";
        $log_query .= "'" . $status . "' , ";
        $log_query .= "'" . $response . "', ";
        $log_query .= "'" . $etablissement . "',";
        $log_query .= "'" . $table . "'";
        $log_query .= ")";

        try {
            $connection = $this->entityManager->getConnection();
            $connection->beginTransaction();
            $connection->executeQuery($log_query);
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            throw new RuntimeException("Problème lors de l'écriture du log en base.", null, $e);
        }

        $this->log($response, $end_date);
    }

    /**
     * Ajoute aux logs les metadonnées associées à une entity
     *
     * @param string $dataName    : le nom de l'entité
     * @param string $entityClass : le chemin vers l'entité (namespace)
     */
    public function logMetadata($dataName, $entityClass)
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;

        $str = <<<EOS
La table associée à l'entité [$dataName][$entityClass] est [$tableName]"
Relation dans propriétés/colonnes: 
EOS;
        foreach ($tableRelation as $propriete => $colonne) {
            $str .= "  - " . $propriete . " => " . $colonne . " [" . $metadata->fieldMappings[$propriete]["type"] . "]" . PHP_EOL;
        }

        $this->log($str);
    }

    /**
     * Mise sous forme de table des données appartenant à une entité
     *
     * @param mixed $entity_json : le json associé à une entité
     * @param mixed metadata : les metadonnées associées à lentité
     * @return array les données mises sous forme d'un tableau
     */
    public function generateValueArray($entity_json, $metadata)
    {
        $valuesArray = [];
        foreach ($metadata->columnNames as $propriete => $colonne) {
            $type = $metadata->fieldMappings[$propriete]["type"];
            $f = new EtablissementPrefixFilter();
            $value = null;
            switch ($propriete) {
                case "etablissementId":
                    $value = $this->etablissement->getCode();
                    break;
                case "sourceCode":
                    $value = $f->addPrefixTo($entity_json->id, $this->etablissement);
                    break;
                case "sourceId":
                case "individuId":
                case "roleId":
                case "theseId":
                case "doctorantId":
                case "structureId":
                case "ecoleDoctId":
                case "uniteRechId":
                    if (isset($entity_json->{$propriete})) {
                        $value = $f->addPrefixTo($entity_json->{$propriete}, $this->etablissement);
                    }
                    break;
                default:
                    if (isset($entity_json->{$propriete})) {
                        $value = $entity_json->{$propriete};
                    }
                    break;
            }

            $valuesArray[] = $this->prepValue($value, $type);
        }

        return $valuesArray;
    }

    /**
     * Génère le code SQL de la requête de suppression des données existantes.
     *
     * @param string $tableName
     * @param array  $filters
     * @return string
     */
    private function generateSQLSnippetForDelete($tableName, array $filters = [])
    {
        $filters = $this->prepareFiltersForTmpTableUpdate($filters);

        $query = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->etablissement->getCode() . "'";

        if (count($filters) > 0) {
            $wheres = $filters;
            // NB: préfixage par le code établissement doit être fait en amont
            array_walk($wheres, function (&$v, $k) {
                $v = strtoupper($k) . " = '$v'";
            });
            $wheres = implode(' AND ', $wheres);
            $query .= ' AND ' . $wheres;
        }

        return $query;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function prepareFiltersForWebServiceRequest(array $filters)
    {
        if (empty($filters)) {
            return $filters;
        }

        $filtersToMerge = [];

        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'these_id':
                    // on remplace l'id par le source code car les WS ne traitent que des sources codes.
                    /** @var These $these */
                    $these = $this->entityManager->getRepository(These::class)->find($value);
                    if ($these === null) {
                        throw new RuntimeException("Aucune thèse trouvée avec cet id: $value");
                    }
                    $f = new EtablissementPrefixFilter();
                    $filtersToMerge['these_id'] = $f->removePrefixFrom($these->getSourceCode());
                    break;
                default:
                    break;
            }
        }

        return array_merge($filters, $filtersToMerge);
    }

    /**
     * @param array $filters
     * @return array
     */
    private function prepareFiltersForTmpTableUpdate(array $filters)
    {
        if (empty($filters)) {
            return $filters;
        }

        $filtersToMerge = [];

        foreach ($filters as $name => $value) {
            switch ($name) {
                case 'these_id':
                    // on remplace l'id par le source code car les WS ne traitent que des sources codes.
                    /** @var These $these */
                    $these = $this->entityManager->getRepository(These::class)->find($value);
                    if ($these === null) {
                        throw new RuntimeException("Aucune thèse trouvée avec cet id: $value");
                    }
                    $filtersToMerge['these_id'] = $these->getSourceCode();
                    break;
                default:
                    break;
            }
        }

        return array_merge($filters, $filtersToMerge);
    }
}