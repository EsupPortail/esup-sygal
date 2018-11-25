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
    protected $etablissement;

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
     * @var string
     */
    protected $serviceName;

    /**
     * @param string $serviceName
     * @return self
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    /**
     * @var ClassMetadata
     */
    private $entityClassMetadata;

    /**
     * @var string
     */
    private $tableName;

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

        $this->logMetadata($this->serviceName, $entityClass);
    }

    /**
     * Demande au WS d'import un enregistrement particulier d'un service.
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * @param stdClass $json
     * @param string   $sourceCode Source code de l'enregistrement à importer
     */
    public function saveEntityForService(stdClass $json, $sourceCode)
    {
        $this->loadEntityClassMetadata();

        $tableColumns = $this->entityClassMetadata->columnNames;

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $queries = [];

        /** Remplissage avec les données retournées par le Web Services */
        $colonnes = implode(", ", $tableColumns);
        $values = implode(", ", $this->generateValueArray($json, $this->entityClassMetadata));
        $query = "INSERT INTO " . $this->tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
        $queries[] = $query;

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            $this->logger->debug("Execution de la requête : " . $query);
            try {
                $connection->executeQuery($query);
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur lors de l'insert dans la table '$this->tableName'.'", null, $e);
            }
        }
        $_fin = microtime(true);

        $this->logger->debug(sprintf("Exécution des %d requêtes INSERT : %s secondes.", count($queries), $_fin - $_debut));
    }

    /**
     * Demande au WS d'import tous les enregistrements d'un service, éventuellement filtrés.
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * @param array $jsonEntities
     */
    public function saveEntitiesForService($jsonEntities)
    {
        $this->loadEntityClassMetadata();

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $tableColumns = $this->entityClassMetadata->columnNames;

        /** Construction des requêtes d'INSERTion **/
        $queries = [];
        foreach ($jsonEntities as $entry) {
            $colonnes = implode(", ", $tableColumns);
            $values = implode(", ", $this->generateValueArray($entry, $this->entityClassMetadata));
            $query = "INSERT INTO " . $this->tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
            $queries[] = $query;
        }

        /** Execution des requetes d'INSERTion groupées */
        $_debut = microtime(true);
        $queriesChunks = array_chunk($queries, self::INSERT_QUERIES_CHUNK_SIZE);
        foreach ($queriesChunks as $queryChunk) {
            $sql = implode(';' . PHP_EOL, $queryChunk) . ';';
            $this->logger->debug(sprintf("Execution de %s requêtes d'insert.", count($queryChunk)));
            try {
                $connection->executeQuery('BEGIN' . PHP_EOL . $sql . PHP_EOL . ' END;');
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur lors des inserts dans la table '$this->tableName'.'", null, $e);
            }
        }
        $_fin = microtime(true);

        $this->logger->debug(sprintf("Exécution des %d requêtes INSERT : %s secondes.", count($queries), $_fin - $_debut));
    }

    /**
     * Supprime les données de la table utilisée pour le service spécifié.
     * NB: AUCUN COMMIT N'EST FAIT.
     *
     * $filters peut contenir une clé 'id' pour ne supprimer qu'un seul enregistrement.
     *
     * @param array $filters
     */
    public function deleteExistingDataForService(array $filters = [])
    {
        $this->loadEntityClassMetadata();

        // Requête de suppression des données existantes
        $query = $this->generateSQLSnippetForDelete($this->tableName, $filters);

        $connection = $this->entityManager->getConnection();
        try {
            $connection->executeQuery($query);
        } catch (DBALException $e) {
            throw new RuntimeException("Erreur lors de la suppression dans la table $this->tableName", null, $e);
        }
    }

    /**
     * Fonction écrivant dans la table des logs API_LOGS.
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
        $end_date = new DateTime();

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
     * Fonction de mise en forme des données typées pour les requêtes Oracle
     *
     * @param mixed  $value : la value à formater
     * @param string $type  : le type de la donnée à formater
     * @return string la donnée formatée
     *
     * RMQ si un format n'est pas prévu par le traitement la valeur est retournée sans traitement et un message est
     * affiché
     */
    private function prepValue($value, $type)
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

    private function prepString($value)
    {
        return ("'" . str_replace("'", "''", $value) . "'");
    }

    private function prepDate($value)
    {
        if ($value === null) {
            return "null";
        }
        $date = explode(' ', $value->{'date'})[0];

        return "to_date('" . $date . "','YYYY-MM-DD')";
    }

    /**
     * Mise sous forme de table des données appartenant à une entité
     *
     * @param stdClass      $jsonEntity : le json associé à une entité
     * @param ClassMetadata $metadata : les metadonnées associées à l'entité
     * @return array les données mises sous forme d'un tableau
     */
    private function generateValueArray(stdClass $jsonEntity, $metadata)
    {
        $f = new EtablissementPrefixFilter();
        $valuesArray = [];
        foreach ($metadata->columnNames as $propriete => $colonne) {
            $type = $metadata->fieldMappings[$propriete]["type"];
            $value = null;
            switch ($propriete) {
                case "etablissementId": // UCN, URN, ULHN, INSA, rien d'autre
                    $value = $this->etablissement->getStructure()->getCode();
                    break;
//                case "acteurEtablissementId":
//                    if (isset($entity_json->{$propriete})) {
//                        $value = $f->addPrefixEtablissementTo($entity_json->{$propriete}, $this->etablissement);
//                    }
//                    break;
                case "sourceCode":
                    $value = $f->addPrefixEtablissementTo($jsonEntity->id, $this->etablissement);
                    break;
                case "sourceId":
                case "individuId":
                case "roleId":
                case "theseId":
                case "doctorantId":
                case "structureId":
                case "ecoleDoctId":
                case "uniteRechId":
                case "acteurEtablissementId":
                case "origineFinancementId":
                    if (isset($jsonEntity->{$propriete})) {
                        $value = $f->addPrefixEtablissementTo($jsonEntity->{$propriete}, $this->etablissement);
                    }
                    break;
                default:
                    if (isset($jsonEntity->{$propriete})) {
                        $value = $jsonEntity->{$propriete};
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

        $query = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->etablissement->getStructure()->getCode() . "'";

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

    /**
     * Ajoute aux logs les metadonnées associées à une entity
     *
     * @param string $dataName    : le nom de l'entité
     * @param string $entityClass : le chemin vers l'entité (namespace)
     */
    private function logMetadata($dataName, $entityClass)
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

        $this->logger->debug($str);
    }
}