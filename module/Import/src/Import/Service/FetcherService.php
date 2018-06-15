<?php

namespace Import\Service;

use Application\Filter\EtablissementPrefixFilter;
use DateTime;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use UnicaenApp\Exception\RuntimeException;
use Zend\Http\Response;

/**
 * FetcherService est un service dédié à la récupération des données provenant du Web Service fournit par chaque
 * établissement.
 *
 * Il est interrogable par le biais de deux fonctions :
 * - fetchAll($dataName, $entityClass) qui récupére toutes les données associés à une table
 * - fetchOne($dataName, $entityClass, source_code) qui récupére les données associés à une entité
 *
 * Chaque appel à ce service va insérer une ligne de log dans la table API_LOGS
 */
class FetcherService
{
    /**
     * Appels ORDONNÉS de procédures de synchronisation à lancer en fonction du service.
     */
    const APP_IMPORT_PROCEDURE_CALLS = [
        'structure' => [
            "UNICAEN_IMPORT.MAJ_STRUCTURE();",
        ],
        'etablissement' => [
            "UNICAEN_IMPORT.MAJ_ETABLISSEMENT();",
        ],
        'ecole-doctorale' => [
            "UNICAEN_IMPORT.MAJ_ECOLE_DOCT();",
            "APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');",
        ],
        'unite-recherche' => [
            "UNICAEN_IMPORT.MAJ_UNITE_RECH();",
        ],
        'individu' => [
            "UNICAEN_IMPORT.MAJ_INDIVIDU();",
        ],
        'doctorant' => [
            "UNICAEN_IMPORT.MAJ_DOCTORANT();",
            "APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');",
        ],
        'these' => [
            "UNICAEN_IMPORT.MAJ_THESE();",
            "APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');",
        ],
        'role' => [
            "UNICAEN_IMPORT.MAJ_ROLE();",
        ],
        'acteur' => [
            "UNICAEN_IMPORT.MAJ_ACTEUR();",
            "APP_IMPORT.REFRESH_MV('MV_RECHERCHE_THESE');",
        ],
        'variable' => [
            "UNICAEN_IMPORT.MAJ_VARIABLE();",
        ],
    ];

    /**
     * $entityManager et $config sont fournis par la factory et permettent l'acces à la BD et à la config
     *
     * @var EntityManager $entityManager
     * @var array         $config
     */
    protected $entityManager;
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
    protected $code;
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

    /**
     * Accessors ...
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
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

    /** Cette fonction retourne la position d'un extablissement dans la table des établissements (voir config)
     *
     * @param string $etablissement
     * @return int
     * @throws \Exception;
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
        $this->code = $this->config['import-api']['etablissements'][$positionEtablissement]['code'];
        $this->url = $this->config['import-api']['etablissements'][$positionEtablissement]['url'];
        $this->proxy = $this->config['import-api']['etablissements'][$positionEtablissement]['proxy'];
        $this->verify = $this->config['import-api']['etablissements'][$positionEtablissement]['verify'];
        $this->user = $this->config['import-api']['etablissements'][$positionEtablissement]['user'];
        $this->password = $this->config['import-api']['etablissements'][$positionEtablissement]['password'];
    }

    /** Fonction chargée d'optenir la réponse d'un Web Service
     *
     * @param string $uri : la "page" du Web Service à interroger
     * @return Response la réponse du Web Service
     *
     * RMQ le client est configuré en utilisant les propriétés du FetcherService
     *
     * @throws \Exception
     */
    public function getResponse($uri)
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
    public function doLog($start_date, $end_date, $route, $status, $response, $etablissement, $table)
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
                    $value = $this->code;
                    break;
                case "sourceCode":
                    $value = $f->addPrefixTo($entity_json->id, $this->code);
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
                        $value = $f->addPrefixTo($entity_json->{$propriete}, $this->code);
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
     * Fonction en charge de préparer et d'appelé fetchOne ou fetchAll
     *
     * @param string $dataName
     * @param string $entityClass
     * @param mixed  $source_code
     * @return array [DateTime, string] le log associé pour l'affichage ...
     * @throws \Exception
     */
    public function fetch($dataName, $entityClass, $source_code = null)
    {
        $logs = [];
        if ($source_code !== null) {
            $logs = $this->fetchOne($dataName, $entityClass, $source_code, 0);
        } else {
            $logs = $this->fetchAll($dataName, $entityClass, 0);
        }

        return $logs;

    }

    /**
     * Lance la synchro des données par UnicaenImport pour les services spécifiés.
     *
     * @param array $services
     * @throws \Doctrine\DBAL\DBALException
     */
    public function updateBDD(array $services)
    {
        // détermination des appels de procédures de synchro à faire
        $calls = [];
        foreach ($services as $service) {
            $calls = array_merge($calls, self::APP_IMPORT_PROCEDURE_CALLS[$service]);
        }
        // suppression des appels en double EN CONSERVANT LE DERNIER appel et non le premier
        $calls = array_reverse(array_unique(array_reverse($calls)));

        $plsql = implode(PHP_EOL, array_merge(['BEGIN'], $calls, ['END;']));

        $this->entityManager->getConnection()->executeQuery($plsql);
    }

    /**
     * @param     $dataName
     * @param     $entityClass
     * @param     $sourceCode
     * @param int $debug_level
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
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

        return $this->doLog(
            $start_date, new DateTime(),
            $this->url . "/" . $dataName . "/" . $sourceCode,
            "OK",
            "Récupération de " . $dataName . ":" . $sourceCode . " de [" . $this->code . "] en " . ($_fin - $debut) . " seconde(s).",
            $this->code,
            "variable");
    }

    /**
     * @param     $dataName
     * @param     $entityClass
     * @param int $debug_level
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
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
        $jsonName = str_replace("-", "_", $dataName);
        $collection_json = $json->{'_embedded'}->{$jsonName};

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

        return $this->doLog($start_date, new DateTime(), $this->url . "/" . $dataName, "OK", count($collection_json) . " " . $dataName . "(s) ont été récupéré(es) de [" . $this->code . "] en " . ($fin - $debut) . " seconde(s).",
            $this->code,
            $dataName        );
    }

}