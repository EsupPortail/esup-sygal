<?php

namespace Import\Service;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Zend\Http\Response;
use DateTime;

/**
 * FetcherService est un service dédié à la récupération des données provenant du Web Service fournit par chaque
 * établissement.
 *
 * Il est interrogable par le biais de deux fonctions :
 * - fetchAll($dataName, $entityClass) qui récupére toutes les données associés à une table
 * - fetchOne($dataName, $entityClass, source_code) qui récupére les données associés à une entité
 *
 * Chaque appel à ce service va insérer une ligne de log dans la table API_LOGS
 *
 * TODO mieux gérer les messages d'erreurs provenant de l'echec de récupération via le Web Service
 * TODO si l'accés à la base de donnée échoue comment mettre le log dans API_LOGS
 * TODO finir de factoriser le code commun entre fetchOne et fetchAll
 */
class FetcherService
{

    /**
     * $entityManager et $config sont fournis par la factory et permettent l'acces à la BD et à la config
     * @var EntityManager $entityManager
     * @var array $config
     */
    protected $entityManager;
    protected $config;

    /**
     * les quatres variables $url, $code, $user et $password sont des données de configuration pour l'acces au Web Service
     * @var string $url : le chemin d'acces au web service
     * @var string $code : le code de l'établissement
     * @var string $user : l'identifiant pour l'authentification
     * @var string $password : le mot de passe pour l'authentification
     */
    protected $url;
    protected $code;
    protected $user;
    protected $password;

    /**
     * Constructor ...
     * @param EntityManager $entityManager
     * @param array $config
     */
    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->user = $config['users']['login'];
        $this->password = $config['users']['password'];
        $this->url = $config['import-api']['etablissements'][0]['url'];
        $this->code = $config['import-api']['etablissements'][0]['code'];
    }

    /**
     * Accessors ...
     */
    public function getEntityManager()    {
        return $this->entityManager;
    }
    public function setEntityManager(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }
    public function getUrl() {
        return $this->url;
    }
    public function setUrl(string $url)     {
        $this->url = $url;
    }
    public function getUser()     {
        return $this->user;
    }
    public function setUser(string $user)     {
        $this->user = $user;
    }
    public function getPassword()     {
        return $this->password;
    }
    public function setPassword(string $password)    {
        $this->password = $password;
    }
    public function getCode()    {
        return $this->code;
    }
    public function setCode($code)    {
        $this->code = $code;
    }

    /**
     * Cette fonction recherche dans le fichier de config l'adresse du Web Service associé à un établissement
     * @param string $etablissement
     */
    public function setUrlWithEtablissement($etablissement)
    {
        $found_url = null;
        $nbEtablissements = count($this->config['import-api']['etablissements']);
        for ($positionEtablissement = 0; $positionEtablissement < $nbEtablissements; ++$positionEtablissement) {
            if ($this->config['import-api']['etablissements'][$positionEtablissement]['code'] == $etablissement) {
                $found_url = $this->config['import-api']['etablissements'][$positionEtablissement]['url'];
            }
        }
        if ($found_url === null) {
            print "<span style='background-color:salmon;'> L'URL associé à l'établissement [" . $etablissement . "] n'a pas pu être trouvée.</span><br/>";
        }
        $this->url = $found_url;
    }

    /** Fonction chargée d'optenir la réponse d'un Web Service
     * @param string $uri : la "page" du Web Service à interroger
     * @return Response la réponse du Web Service
     *
     * RMQ le client est configuré en utilisant les propriétés du FetcherService
     *
     * TODO mettre automatique le proxy
     */
    public function getResponse($uri)
    {
        try {
            $client = new Client([
                'base_uri' => $this->url,
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'proxy' => ['no' => 'localhost'],
                'auth' => [$this->user, $this->password],
            ]);
            $response = $client->request('GET', $uri);
        } catch (\Exception $e) {
            $response = new Response();
            $response->setStatusCode(500);
            $response->setReasonPhrase($e->getCode()." - ".$e->getMessage());
            throw $e;
        }
        return $response;
    }

    /**
     * Fonction de mise en forme des données typées pour les requêtes Oracle
     * @param mixed $value : la value à formater
     * @param string $type : le type de la donnée à formater
     * @return string la donnée formatée
     *
     * RMQ si un format n'est pas prévu par le traitement la valeur est retournée sans traitement et un message est affiché
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
     * @param DateTime $start_date : date de début du traitement
     * @param DateTime $end_date : date de fin (avec succés ou échec) du traitement
     * @param string $route : la route du traitement
     * @param string status : le status du traitement
     * @param string $response : le message associé au traitement
     * @return array [DateTime, string] le log associé pour l'affichage ...
     * @throws \Exception
     * */
    public function doLog($start_date, $end_date, $route, $status, $response)
    {
        $log_query = "INSERT INTO API_LOG ";
        $log_query .= "(ID, REQ_URI, REQ_START_DATE, REQ_END_DATE, REQ_STATUS, REQ_RESPONSE)";
        $log_query .= " values (";
        $log_query .= "API_LOG_ID_SEQ.nextval ,";
        $log_query .= "'" . $route . "' , ";
        $log_query .= "to_date('" . $start_date->format('y-m-d h:m:s') . "','YY-MM-DD HH24:MI:SS') , ";
        $log_query .= "to_date('" . $end_date->format('y-m-d h:m:s') . "','YY-MM-DD HH24:MI:SS') , ";
        $log_query .= "'" . $status . "' , ";
        $log_query .= "'" . $response . "'";
        $log_query .= ")";

        try {
            $connection = $this->entityManager->getConnection();
            $connection->beginTransaction();
            $connection->executeQuery($log_query);
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            return [$end_date, $e->getCode()." - ".$e->getMessage() ];
        }

        return [$end_date, $response];
    }

    /**
     * Fonction en charge de l'affichage des metadonnées associées à une entity
     * @param string $dataName : le nom de l'entité
     * @param string $entityClass : le chemin vers l'entité (namespace)
     */
    public function displayMetadata($dataName, $entityClass) {
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
     * @param mixed $entity_json : le json associé à une entité
     * @param mixed metadata : les metadonnées associées à lentité
     * @return array les données mises sous forme d'un tableau
     */
    public function generateValueArray($entity_json, $metadata)
    {
        $valuesArray = [];
        foreach ($metadata->columnNames as $propriete => $colonne) {
            $type = $metadata->fieldMappings[$propriete]["type"];
            $value = null;
            if ($propriete === "etablissementId")   $value = $this->code;
            elseif ($propriete === "sourceCode")    $value = $this->code."::".$entity_json->{'id'};
            elseif ($propriete === "sourceId")      $value = $this->code."::".$entity_json->{'sourceId'};
            elseif ($propriete === "individuId")    $value = $this->code."::".$entity_json->{'individuId'};
            else $value = $entity_json->{$propriete};
            $valuesArray[] = $this->prepValue($value, $type);
        }
        return $valuesArray;
    }

    /**
     * Fonction en charge de préparer et d'appelé fetchOne ou fetchAll
     * @param string $dataName
     * @param string $entityClass
     * @param mixed $source_code
     * @return array [DateTime, string] le log associé pour l'affichage ...
     * @throws \Exception
     */
    public function fetch($dataName, $entityClass, $source_code = null)
    {
        if ($source_code !== null) return $this->fetchOne($dataName, $entityClass, $source_code);
        return $this->fetchAll($dataName, $entityClass);
    }
    public function fetchOne($dataName, $entityClass, $sourceCode, $debug_level = 0)
    {
        $debut = microtime(true);
        $start_date = new DateTime();

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($debug_level > 1) $this->displayMetadata($dataName, $entityClass);

        /** Appel du web service */
        $_debut = microtime(true);
        $response = $this->getResponse($dataName . "/" . $sourceCode);
        if ($response->getStatusCode() != 200) return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName . "/" . $sourceCode, "ERROR_WS", $response->getReasonPhrase());
        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> WebService: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $_debut = microtime(true);
        /** Vidage des données de la table */
        $queries = [];
        $queries[] = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->code . "' AND ID='" . $sourceCode . "'";

        /** Remplissage avec les données retournées par le Web Services */
        $json = json_decode($response->getBody());
        $colonnes = implode(", ", $tableRelation);
        $values = implode(", ", $this->generateValueArray($json, $metadata));
        $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
        $queries[] = $query;
        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> PrepQueries: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            if ($debug_level > 1) print "Execution de la requête [<tt>" . $query . "</tt>] <br/>";
            $statement = $connection->executeQuery($query);
        }
        try {
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName . "/" . $sourceCode, "ERROR_DB", $e->getMessage());
        }
        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> ExecQueries: " . ($_fin - $_debut) . " secondes.</span></p>";
        return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName . "/" . $sourceCode, "OK", "Récupération de ".$dataName.":".$sourceCode." de [" . $this->code . "] en " . ($_fin - $debut) . " seconde(s).");
    }
    public function fetchAll($dataName, $entityClass, $debug_level = 0)
    {
        $start_date = new DateTime();
        $debut = microtime(true);

        if ($debug_level > 0) print "<hr/><h2>" . $dataName . "</h2>";

        /** Récupération des infos et des champs (i.e. propriétés/colonnes) */
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        $tableName = $metadata->table['name'];
        $tableRelation = $metadata->columnNames;
        if ($debug_level > 1) $this->displayMetadata($dataName, $entityClass);

        /** Appel du web service */
        $_debut = microtime(true);
        $response = $this->getResponse($dataName);
        if ($response->getStatusCode() != 200) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName, "ERROR_WS", $response->getReasonPhrase());
        }
        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> WebService: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Etablissement de la transaction Oracle */
        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();
        $statement = null;

        $_debut = microtime(true);
        /** Vidage des données de la table */
        $queries = [];
        $queries[] = "DELETE FROM " . $tableName . " WHERE ETABLISSEMENT_ID='" . $this->code . "'";

        /** Remplissage avec les données retournées par le Web Services */
        $json = json_decode($response->getBody());
        $collection_json = $json->{'_embedded'}->{$dataName};
        foreach ($collection_json as $entity_json) {
            $colonnes = implode(", ", $tableRelation);
            $values = implode(", ", $this->generateValueArray($entity_json, $metadata));
            $query = "INSERT INTO " . $tableName . " (" . $colonnes . ") VALUES (" . $values . ")";
            $queries[] = $query;
        }
        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> PrepQueries: " . ($_fin - $_debut) . " secondes.</span></p>";

        /** Execution des requetes */
        $_debut = microtime(true);
        foreach ($queries as $query) {
            if ($debug_level > 1) print "Execution de la requête [<tt>" . $query . "</tt>] <br/>";
            $statement = $connection->executeQuery($query);
        }

        try {
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName, "ERROR_DB", $e->getMessage());
        }

        $_fin = microtime(true);
        if ($debug_level > 0) print "<p><span style='background-color:lightgreen;'> ExecQueries: " . ($_fin - $_debut) . " secondes.</span></p>";
        $fin = microtime(true);

        return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName, "OK", count($collection_json) . " " . $dataName . "(s) ont été récupéré(es) de [" . $this->code . "] en " . ($fin - $debut) . " seconde(s).");
    }

}