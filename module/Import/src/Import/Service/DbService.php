<?php

namespace Import\Service;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\These;
use Application\Filter\EtablissementPrefixFilter;
use DateTime;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping\ClassMetadata;
use stdClass;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Log\LoggerAwareTrait;

/**
 * Service dédié la persistance des données retournées par le Web Service.
 */
class DbService
{
    use EntityManagerAwareTrait;
    use LoggerAwareTrait;

    const INSERT_QUERIES_CHUNK_SIZE = 200;

    /**
     * @var Etablissement
     */
    private $etablissement;

    /**
     * @var string
     */
    protected $serviceName;

    /**
     * @var ClassMetadata
     */
    private $entityClassMetadata;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var SQLGenerator
     */
    private $sqlGenerator;

    /**
     * @var JSONExtractor
     */
    private $jsonExtractor;

    /**
     * DbService constructor.
     */
    public function __construct()
    {
        $this->sqlGenerator = new SQLGenerator();
        $this->jsonExtractor = new JSONExtractor();
    }

    /**
     * @param Etablissement $etablissement
     * @return self
     */
    public function setEtablissement(Etablissement $etablissement)
    {
        $this->etablissement = $etablissement;

        $this->jsonExtractor->setEtablissement($etablissement);

        return $this;
    }

    /**
     * @param string $serviceName
     * @return self
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;

        $this->entityClassMetadata = null;

        return $this;
    }

    /**
     * Persiste en base de données les données importées spécifiées, concernant le service et l'établissement courants.
     *
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * @param stdClass[] $jsonEntities
     */
    public function save(array $jsonEntities)
    {
        $this->loadEntityClassMetadata();

        $tableColumns = $this->entityClassMetadata->columnNames;

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        /** Construction des requêtes SQL **/
        $queries = [];
        foreach ($jsonEntities as $jsonEntity) {
            $columnsValues = $this->generateValueArray($jsonEntity);
            $queries[] = $this->sqlGenerator->generateSQLQueryForSavingData($this->tableName, $tableColumns, $columnsValues);
        }

        /** Execution des requetes SQL par groupes de N */
        $_debut = microtime(true);
        $queriesChunks = array_chunk($queries, self::INSERT_QUERIES_CHUNK_SIZE);
        foreach ($queriesChunks as $queryChunk) {
            $this->logger->debug(sprintf("Execution de %s requête(s).", count($queryChunk)));
            $sql = $this->sqlGenerator->wrapSQLQueriesInBeginEnd($queryChunk);
            try {
                $connection->executeQuery($sql);
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur rencontrée lors des requêtes dans '$this->tableName'.'", null, $e);
            }
        }
        $_fin = microtime(true);

        $this->logger->debug(sprintf("%d requête(s) effectuée(s) en %s secondes.", count($queries), $_fin - $_debut));
    }

    /**
     * Supprime certaines données en base satisfaisant les critères spécifiés,
     * concernant le service et l'établissement courants.
     *
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * @param array $filters Peut contenir une clé 'id' pour ne supprimer qu'un seul enregistrement.
     */
    public function clear(array $filters = [])
    {
        $this->loadEntityClassMetadata();

        $filters = $this->prepareFiltersForTableUpdate($filters);
        $filters['etablissement_id'] = $this->etablissement->getStructure()->getCode();

        $query = $this->sqlGenerator->generateSQLQueryForClearingExistingData($this->tableName, $filters);

        $connection = $this->entityManager->getConnection();
        try {
            $connection->executeQuery($query);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur lors de la suppression dans la table $this->tableName", null, $e);
        }
    }

    /**
     * Fait un commit en bdd.
     */
    public function commit()
    {
        $connection = $this->entityManager->getConnection();

        $_debut = microtime(true);
        try {
            $connection->commit();
        } catch (\Exception $e) {
            try {
                $connection->rollBack();
            } catch (ConnectionException $e) {
                throw new RuntimeException("Le rollback a échoué!", null, $e);
            }
            throw new RuntimeException("Le commit a échoué, un rollback a été effectué.", null, $e);
        }
        $_fin = microtime(true);

        $this->logger->debug("Commit : " . ($_fin - $_debut) . " secondes.");
    }

    /**
     * Récupération des infos et des champs (i.e. propriétés/colonnes)
     */
    private function loadEntityClassMetadata()
    {
        if ($this->entityClassMetadata !== null) {
            return;
        }

        $entityClass = $this->entityNameForService($this->serviceName);

        $this->entityClassMetadata = $this->entityManager->getClassMetadata($entityClass);
        $this->tableName = $this->entityClassMetadata->table['name'];

        $this->sqlGenerator->setDatabasePlatform($this->entityManager->getConnection()->getDatabasePlatform());

        $this->logMetadata();
    }

    /**
     * Fonction écrivant dans la table des logs API_LOGS.
     *
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * @param string        $serviceName
     * @param DateTime      $startDate : date de début du traitement
     * @param float         $duration
     * @param string        $route     : la route du traitement
     * @param string        $status
     */
    public function insertLog($serviceName, DateTime $startDate, $duration, $route, $status)
    {
        try {
            $end_date = new DateTime();
        } catch (\Exception $e) {
            throw new RuntimeException("Là, on touche le fond!", null, $e);
        }

        $message = sprintf("Interrogation du service '%s' de l'établissement '%s', en %s seconde(s).",
            $serviceName,
            $this->etablissement->getStructure()->getCode(),
            $duration
        );

        $sql = "INSERT INTO API_LOG ";
        $sql .= "(ID, REQ_URI, REQ_START_DATE, REQ_END_DATE, REQ_STATUS, REQ_RESPONSE, REQ_ETABLISSEMENT, REQ_TABLE) values (";
        $sql .= "API_LOG_ID_SEQ.nextval,";
        $sql .= ":route, ";
        $sql .= "to_date('" . $startDate->format('y-m-d H:i:s') . "','YY-MM-DD HH24:MI:SS'), ";
        $sql .= "to_date('" . $end_date->format('y-m-d H:i:s') . "','YY-MM-DD HH24:MI:SS'), ";
        $sql .= ":status, ";
        $sql .= ":message, ";
        $sql .= ":etab,";
        $sql .= ":service";
        $sql .= ")";

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        try {
            $connection->executeQuery($sql, [
                'message' => $message,
                'etab' => $this->etablissement->getStructure()->getCode(),
                'status' => $status,
                'route' => $route,
                'service' => $serviceName,
            ]);
        } catch (DBALException $e) {
            throw new RuntimeException("Ecriture du log en base impossible.", null, $e);
        }

        $this->logger->info($message);
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
     * @param array $filters
     * @return array
     */
    private function prepareFiltersForTableUpdate(array $filters)
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

    /**
     * Mise sous forme de liste de valeurs des attributs d'une entité JSON, dans l'ordre des colonnes de la table.
     *
     * @param stdClass $jsonEntity : le json associé à une entité
     * @return array les données mises sous forme d'un tableau
     */
    private function generateValueArray(stdClass $jsonEntity)
    {
        $valuesArray = [];

        foreach ($this->entityClassMetadata->columnNames as $property => $columnName) {
            $value = $this->jsonExtractor ->extractPropertyValue($property, $jsonEntity);
            $type = $this->entityClassMetadata->fieldMappings[$property]["type"];

            $valuesArray[] = $this->sqlGenerator->formatValueForPropertyType($value, $type);
        }

        return $valuesArray;
    }

    /**
     * Log les metadonnées associées au service courant.
     */
    private function logMetadata()
    {
        $entityClass = $this->entityClassMetadata->name;
        $tableName = $this->entityClassMetadata->table['name'];
        $tableColumns = $this->entityClassMetadata->columnNames;

        $str = <<<EOS
La table associée à l'entité [$this->serviceName][$entityClass] est [$tableName]"
Relation dans propriétés/colonnes: 
EOS;
        foreach ($tableColumns as $propriete => $colonne) {
            $type = $this->entityClassMetadata->fieldMappings[$propriete]["type"];
            $str .= "  - " . $propriete . " => " . $colonne . " [" . $type . "]" . PHP_EOL;
        }

        $this->logger->debug($str);
    }
}
