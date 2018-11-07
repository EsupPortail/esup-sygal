<?php

namespace Import\Service;

use Assert\Assertion;
use Assert\AssertionFailedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Import\Exception\CallException;
use Zend\Http\Response;
use Zend\Log\LoggerAwareTrait;

/**
 * Service dédié à l'envoi de requêtes au Web Service.
 */
class CallService
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string         $url      : le chemin d'acces au web service
     * @var string         $user     : l'identifiant pour l'authentification
     * @var string         $password : le mot de passe pour l'authentification
     * @var string|null    $proxy    : le champ proxy
     * @var boolean|string $verify   : le champ pour le mode https
     * @var float          $timeout  : timeout of the request in seconds
     */
    protected $url;
    protected $user;
    protected $password;
    protected $proxy;
    protected $verify = true;
    protected $timeout = 0;

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function version()
    {
        $uri = 'version/current';

        $response = $this->sendRequest($uri);
        if ($response->getStatusCode() != 200) {
            throw CallException::unexpectedResponse($uri, $response);
        }

        $json = json_decode($response->getBody());
        if ($json === null) {
            // NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
            throw CallException::invalidJSONResponse($uri, (string)$response->getBody());
        }

        return $json;
    }

    /**
     * @param string $uri
     * @return \stdClass
     */
    public function get($uri)
    {
        try {
            $response = $this->sendRequest($uri);
        } catch (\Exception $e) {
            throw CallException::error($uri, $e);
        }
        if ($response->getStatusCode() !== Response::STATUS_CODE_200) {
            throw CallException::unexpectedResponse($uri, $response);
        }

        $json = json_decode($response->getBody());
        if ($json === null) {
            // NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
            throw CallException::invalidJSONResponse($uri, (string)$response->getBody());
        }

        return $json;
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    private function loadConfig()
    {
        Assertion::keyIsset($this->config, 'url');
        Assertion::keyIsset($this->config, 'user');
        Assertion::keyIsset($this->config, 'password');

        $this->url = $this->config['url'];
        $this->user = $this->config['user'];
        $this->password = $this->config['password'];

        if (array_key_exists('proxy', $this->config)) {
            $this->proxy = $this->config['proxy'];
        }
        if (array_key_exists('verify', $this->config)) {
            $this->verify = $this->config['verify'];
        }
        if (array_key_exists('timeout', $this->config)) {
            $this->timeout = $this->config['timeout'];
        }
    }

    private function createClient()
    {
        try {
            $this->loadConfig();
        } catch (AssertionFailedException $e) {
            throw CallException::badConfig($e);
        }

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
        if ($this->timeout !== null) {
            $options['timeout'] = $this->timeout;
        }

        return new Client($options);
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
        $client = $this->createClient();

        try {
            $_debut = microtime(true);
            $response = $client->request('GET', $uri);
            $_fin = microtime(true);
            $this->logger->debug("Interrogation du WS : " . ($_fin - $_debut) . " secondes.");
        } catch (ClientException $e) {
            throw CallException::clientError($e);
        } catch (ServerException $e) {
            throw CallException::serverError($e);
        } catch (RequestException $e) {
            throw CallException::networkError($e);
        } catch (GuzzleException $e) {
            throw CallException::error($uri, $e);
        }

        return $response;
    }
}