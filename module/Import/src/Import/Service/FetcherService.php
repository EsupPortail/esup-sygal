<?php

namespace Import\Service;

use Application\Entity\Db\These;
use Application\Filter\EtablissementPrefixFilter;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Http\Response;

/**
 * FetcherService est un service dédié à la récupération des données provenant du Web Service fournit par chaque
 * établissement.
 *
 * Il est interrogable par le biais de deux fonctions :
 * - fetchAllRows($dataName, $entityClass) qui récupére toutes les données associés à une table
 * - fetchRow($dataName, $entityClass, source_code) qui récupére les données associés à une entité
 *
 * Chaque appel à ce service va insérer une ligne de log dans la table API_LOGS
 */
class FetcherService
{
    use EntityManagerAwareTrait;

    /**
     * $config est fourni par la factory et permet l'acces à la config
     *
     * @var array $config
     */
    protected $config;

    /**
     * les quatres variables $url, $code, $user et $password sont des données de configuration pour l'acces au Web
     * Service
     *
     * @var string         $url      : le chemin d'acces au web service
     * @var string         $code     : le code de l'établissement
     * @var string         $user     : l'identifiant pour l'authentification
     * @var string         $password : le mot de passe pour l'authentification
     * @var string|null    $proxy    : le champ proxy
     * @var boolean|string $verify   : le champ pour le mode https
     */
    protected $url;
    protected $codeEtablissement;
    protected $user;
    protected $password;
    protected $proxy;
    protected $verify;

    /**
     * Constructor ...
     *
     * @param EntityManager $entityManager
     * @param array         $config
     */
    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getCodeEtablissement()
    {
        return $this->codeEtablissement;
    }

    public function setCodeEtablissement($codeEtablissement)
    {
        $this->codeEtablissement = $codeEtablissement;
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function getVerify()
    {
        return $this->verify;
    }

    public function setVerify($verify)
    {
        $this->verify = $verify;
    }

    /**
     * Point d'entrée pour l'interrogation du web service d'import de données d'un établissement.
     *
     * @param string $serviceName
     * @param mixed  $sourceCode Source code avec ou sans préfixe
     * @param array  $filters
     * @return array [DateTime, string] le log associé pour l'affichage ...
     */
    public function fetch($serviceName, $sourceCode = null, array $filters = [])
    {
        $sourceCode = $this->normalizeSourceCode($sourceCode);
        $entityClass = $this->entityNameForService($serviceName);

        if ($sourceCode !== null) {
            $logs = $this->fetchRow($serviceName, $entityClass, $sourceCode);
        } else {
            $logs = $this->fetchRows($serviceName, $entityClass, $filters);
        }

        return $logs;
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
     * @param string $serviceName
     * @param string $entityClass
     * @param string $sourceCode
     * @param int    $debugLevel
     * @return array
     */
    private function fetchRow($serviceName, $entityClass, $sourceCode, $debugLevel = 0)
    {
        $debut = microtime(true);
        $start_date = new DateTime();

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($debugLevel > 1) $this->displayMetadata($serviceName, $entityClass);

        /** Appel du web service */
        $_debut = microtime(true);
        $uri = $serviceName . "/" . $sourceCode;
        try {
            $response = $this->sendRequest($uri);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur lors de l'interrogation du WS", null, $e);
        }
        if ($response->getStatusCode() != 200) return $this->doLog($start_date, new DateTime(), $this->url . "/" . $uri, "ERROR_WS", $response->getReasonPhrase());
        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> WebService: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $_debut = microtime(true);
        /** Vidage des données de la table */
        $queries = [];
        $queries[] = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->codeEtablissement . "' AND ID='" . $sourceCode . "'";

        /** Remplissage avec les données retournées par le Web Services */
        $json = json_decode($response->getBody());
        $colonnes = implode(", ", $tableRelation);
        $values = implode(", ", $this->generateValueArray($json, $metadata));
        $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
        $queries[] = $query;
        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> PrepQueries: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            if ($debugLevel > 1) print "Execution de la requête [<tt>" . $query . "</tt>] <br/>";
            try {
                $connection->executeQuery($query);
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur lors de la mise à jour de la table $tableName en BDD", null, $e);
            }
        }
        try {
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $uri, "ERROR_DB", $e->getMessage());
        }
        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> ExecQueries: " . ($_fin - $_debut) . " secondes.</span></p>";

        return $this->doLog(
            $start_date, new DateTime(),
            $this->url . "/" . $uri,
            "OK",
            "Récupération de " . $serviceName . ":" . $sourceCode . " de [" . $this->codeEtablissement . "] en " . ($_fin - $debut) . " seconde(s).",
            $this->codeEtablissement,
            "variable");
    }

    /**
     * @param string $service
     * @param string $entityClass
     * @param array  $filters
     * @param int    $debugLevel
     * @return array
     */
    public function fetchRows($service, $entityClass, array $filters = [], $debugLevel = 0)
    {
        $debut = microtime(true);
        $start_date = new DateTime();

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($debugLevel > 1) $this->displayMetadata($service, $entityClass);

        /** Appel du web service */
        $_debut = microtime(true);
        $filtersForWebService = $this->prepareFiltersForWebServiceRequest($filters);
        $uri = $service . '?' . http_build_query($filtersForWebService);
        try {
            $response = $this->sendRequest($uri);
        } catch (\Exception $e) {
            throw new RuntimeException("Erreur lors de l'interrogation du WS", null, $e);
        }
        if ($response->getStatusCode() != 200) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $uri, "ERROR_WS", $response->getReasonPhrase());
        }
        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> WebService: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $_debut = microtime(true);
        $queries = [];

        /** Requête de suppression des données existantes */
        $query = $this->generateSQLSnippetForDelete($tableName, $filters);
        $queries[] = $query;

        /** Remplissage avec les données retournées par le Web Services */
        $json = json_decode($response->getBody());
        $jsonName = str_replace("-", "_", $service);
        $collection_json = $json->{'_embedded'}->{$jsonName};

        foreach ($collection_json as $entity_json) {
            $colonnes = implode(", ", $tableRelation);
            $values = implode(", ", $this->generateValueArray($entity_json, $metadata));
            $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
            $queries[] = $query;
        }
        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> PrepQueries: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            if ($debugLevel > 1) print "Execution de la requête [<tt>" . $query . "</tt>] <br/>";
            try {
                $connection->executeQuery($query);
            } catch (DBALException $e) {
                throw new RuntimeException("Erreur lors de la mise à jour de la table $tableName en BDD", null, $e);
            }
        }
        try {
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $uri, "ERROR_DB", $e->getMessage());
        }

        $_fin = microtime(true);
        if ($debugLevel > 0) print "<p><span style='background-color:lightgreen;'> ExecQueries: " . ($_fin - $_debut) . " secondes.</span></p>";
        $fin = microtime(true);

        return $this->doLog(
            $start_date,
            new DateTime(),
            $this->url . "/" . $uri, "OK", count($collection_json) . " " . $service . "(s) ont été récupéré(es) de [" . $this->codeEtablissement . "] en " . ($fin - $debut) . " seconde(s).",
            $this->codeEtablissement,
            $uri
        );
    }

    /** Cette fonction retourne la position d'un extablissement dans la table des établissements (voir config)
     *
     * @param string $etablissement
     * @return int
     */
    public function getEtablissementKey($etablissement)
    {
        $position = -1;
        $nbEtablissements = count($this->config['import-api']['etablissements']);
        for ($positionEtablissement = 0; $positionEtablissement < $nbEtablissements; ++$positionEtablissement) {
            if ($this->config['import-api']['etablissements'][$positionEtablissement]['code'] == $etablissement) {
                return $positionEtablissement;
            }
        }
        if ($position === -1) {
            print "<span style='background-color:salmon;'> L'établissement [" . $etablissement . "] n'a pas pu être trouvée.</span><br/>";
            throw new RuntimeException("L'établissement [" . $etablissement . "] n'a pas pu être trouvée.");
        }

        return $position;
    }

    public function setConfigWithPosition($positionEtablissement)
    {
        $this->codeEtablissement = $this->config['import-api']['etablissements'][$positionEtablissement]['code'];
        $this->url = $this->config['import-api']['etablissements'][$positionEtablissement]['url'];
        $this->proxy = $this->config['import-api']['etablissements'][$positionEtablissement]['proxy'];
        $this->verify = $this->config['import-api']['etablissements'][$positionEtablissement]['verify'];
        $this->user = $this->config['import-api']['etablissements'][$positionEtablissement]['user'];
        $this->password = $this->config['import-api']['etablissements'][$positionEtablissement]['password'];
    }

    /**
     * Appel du Web Service d'import de données.
     *
     * @param string $uri : la "page" du Web Service à interroger
     * @return Response la réponse du Web Service
     *
     * RMQ le client est configuré en utilisant les propriétés du FetcherService
     */
    private function sendRequest($uri)
    {
        $options = [
            'base_uri' => $this->url,
            'headers'  => [
                'Accept' => 'application/json',
            ],
            'auth'     => [$this->user, $this->password],
        ];

        if ($this->proxy !== null) {
            $options['proxy'] = $this->proxy;
        } else {
            $options['proxy'] = ['no' => 'localhost'];
        }

        if ($this->verify !== null) {
            $options['verify'] = $this->verify;
        }

        $client = new Client($options);
        try {
            $response = $client->request('GET', $uri);
        } catch (ClientException $e) {
            throw new RuntimeException("Erreur ClientException rencontrée lors de l'envoi de la requête au WS", null, $e);
        } catch (ServerException $e) {
            $message = "Erreur distante rencontrée par le serveur du WS";
            $previous = null;
            if ($e->hasResponse()) {
                $previous = new RuntimeException($e->getResponse()->getBody());
            }
            throw new RuntimeException($message, null, $previous);
        } catch (RequestException $e) {
            $message = "Erreur réseau rencontrée lors de l'envoi de la requête au WS";
            if ($e->hasResponse()) {
                $message .= " : " . Psr7\str($e->getResponse());
            }
            throw new RuntimeException($message, null, $e);
        } catch (GuzzleException $e) {
            throw new RuntimeException("Erreur inattendue rencontrée lors de l'envoi de la requête au WS", null, $e);
        }

        return $response;
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
            case "string" :
                $nvalue = $this->prepString($value);
                break;
            case "date"   :
                $nvalue = $this->prepDate($value);
                break;
            case "integer"   :
                $nvalue = $value;
                break;
            default       :
                print "<span style='background-color:salmon;'>" . $type . " type de données non géré par prepValue </span>";
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
     * @param string status : le status du traitement
     * @param string   $response   : le message associé au traitement
     * @return array [DateTime, string] le log associé pour l'affichage ...
     * @throws RuntimeException
     * */
    public function doLog($start_date, $end_date, $route, $status, $response, $etablissement = null, $table = null)
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

        return [$end_date, $response];
    }

    /**
     * Fonction en charge de l'affichage des metadonnées associées à une entity
     *
     * @param string $dataName    : le nom de l'entité
     * @param string $entityClass : le chemin vers l'entité (namespace)
     */
    public function displayMetadata($dataName, $entityClass)
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;

        print "La table associée à l'entité [" . $dataName . "][" . $entityClass . "] est [" . $tableName . "]<br/>";
        print "Relation dans propriétés/colonnes: <ul>";
        foreach ($tableRelation as $propriete => $colonne) {
            print "<li>" . $propriete . " => " . $colonne . " [" . $metadata->fieldMappings[$propriete]["type"] . "]" . "</li>";
        }
        print "</ul>";
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
                    $value = $this->codeEtablissement;
                    break;
                case "sourceCode":
                    $value = $f->addPrefixTo($entity_json->id, $this->codeEtablissement);
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
                        $value = $f->addPrefixTo($entity_json->{$propriete}, $this->codeEtablissement);
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

        $query = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->codeEtablissement . "'";

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