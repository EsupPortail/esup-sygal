<?php

namespace Import\Service;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use UnicaenApp\Exception\RuntimeException;
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
     * @var Client
     */
    protected $client;

    /**
     * @param array $config
     * @return self
     */
    public function setConfig(array $config)
    {
        try {
            $this->loadConfig($config);
        } catch (AssertionFailedException $e) {
            throw CallException::badConfig($e);
        }

        return $this;
    }

    /**
     * Interroge le ws pour connaître sa version.
     *
     * @return \stdClass
     */
    public function getVersion()
    {
        return $this->get('version/current');
    }

    /**
     * Envoie une requête quelconque au web service, ex: 'version/current'.
     *
     * @param string $uri
     * @return \stdClass
     */
    public function get($uri)
    {
        $json = $this->sendRequest($uri);

        return $json;
    }

    /**
     * @param array $config
     * @throws AssertionFailedException
     */
    private function loadConfig(array $config)
    {
        Assertion::keyIsset($config, 'url');
        Assertion::keyIsset($config, 'user');
        Assertion::keyIsset($config, 'password');

        $this->config = [
            'base_uri' => $config['url'],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [
                $config['user'],
                $config['password'],
            ],
        ];

        if (array_key_exists('verify', $config)) {
            $this->config['verify'] = $config['verify'];
        }
        if (array_key_exists('timeout', $config)) {
            $this->config['timeout'] = $config['timeout'];
        }
        if (array_key_exists('proxy', $config)) {
            $this->config['proxy'] = $config['proxy'];
        } else {
            $this->config['proxy'] = ['no' => 'localhost'];
        }
    }

    /**
     * @param Client $client
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->client === null) {
            $this->client = $this->createClient();
        }

        return $this->client;
    }

    /**
     * @return Client
     */
    private function createClient()
    {
        if ($this->config === null) {
            throw new RuntimeException("Une config doit être fournie au préalable.");
        }

        return new Client($this->config);
    }

    /**
     * Appel du Web Service d'import de données.
     *
     * @param string $uri : la "page" du Web Service à interroger
     * @return \stdClass Réponse du Web Service décodé en JSON
     *
     * RMQ le client est configuré en utilisant les propriétés du FetcherService
     */
    private function sendRequest($uri)
    {
        $client = $this->getClient();

        try {
            $response = $client->request('GET', $uri);
        } catch (ClientException $e) {
            throw CallException::clientError($e);
        } catch (ServerException $e) {
            throw CallException::serverError($e);
        } catch (RequestException $e) {
            throw CallException::networkError($e);
        } catch (GuzzleException $e) {
            throw CallException::unexpectedError($uri, $e);
        } catch (Exception $e) {
            throw CallException::unexpectedError($uri, $e);
        }

        if ($response->getStatusCode() !== Response::STATUS_CODE_200) {
            throw CallException::unexpectedResponse($uri, $response);
        }

        $body = $response->getBody();

        $json = json_decode($body);
        if ($json === null) {
            // NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
            throw CallException::invalidJSONResponse($uri, (string)$body);
        }

        return $json;
    }
}