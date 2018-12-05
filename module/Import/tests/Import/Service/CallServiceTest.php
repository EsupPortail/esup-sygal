<?php

namespace ImportTest\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Import\Service\CallService;
use stdClass;
use Zend\Http\Response;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use UnicaenApp\Exception\RuntimeException;
use Import\Exception\CallException;
use Zend\Log\LoggerInterface;

class CallServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    protected function setUp()
    {
        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @expectedException \Import\Exception\CallException
     */
    public function testSettingIncompleteConfigThrowsException()
    {
        $config = [];
        $service = new CallService();
        $service->setConfig($config);
    }

    public function testSettingConfigWithMandatoryKeys()
    {
        $config = [
            'url' => 'set',
            'user' => 'set',
            'password' => 'set',
        ];
        $service = new CallService();
        try {
            $service->setConfig($config);
        } catch (\Exception $e) {
            $this->fail("Aucune exception ne devrait être lancée.");
        }
    }

    public function testSettingConfigPopulatesConfig()
    {
        $populatedConfig = [
            'base_uri' => 'url value',
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [
                'user value',
                'password value',
            ],
            'proxy' => 'proxy value',
            'verify' => 'verify ifyvalue',
            'timeout' => 'timeout value',
        ];

        $service = new CallService();
        $service->setConfig([
            'url' => 'url value',
            'user' => 'user value',
            'password' => 'password value',
            'proxy' => 'proxy value',
            'verify' => 'verify ifyvalue',
            'timeout' => 'timeout value',
        ]);

        $this->assertEquals($populatedConfig, $this->getObjectAttribute($service, 'config'));
    }

    public function testSettingConfigPopulatesDefaultProxyValue()
    {
        $populatedConfig = [
            'base_uri' => 'url value',
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth' => [
                'user value',
                'password value',
            ],
            'proxy' => ['no' => 'localhost'], // proxy default value
        ];

        $service = new CallService();
        $service->setConfig([
            'url' => 'url value',
            'user' => 'user value',
            'password' => 'password value',
            //'proxy' => 'proxy value', // no 'proxy' key
        ]);

        $this->assertEquals($populatedConfig, $this->getObjectAttribute($service, 'config'));
    }

    /**
     * @expectedException \UnicaenApp\Exception\RuntimeException
     */
    public function testCreateClientThrowsExceptionWhenNoConfigAvailable()
    {
        $service = new CallService();
        $service->get('peu/importe');
    }

    public function testCanInjectClient()
    {
        $service = new CallService();
        $service->setClient($this->client);

        $this->assertSame($this->client, $service->getClient());
    }

    public function testCanCreateClientIfConfigIsProvided()
    {
        $service = new CallService();
        $service->setConfig([
            'url' => 'url value',
            'user' => 'user value',
            'password' => 'password value',
        ]);

        $this->assertInstanceOf(Client::class, $service->getClient());
    }

    public function getSendRequestPossibleError()
    {
        return [
            [$this->createMock(ClientException::class)],
            [$this->createMock(ServerException::class)],
            [$this->createMock(RequestException::class)],
            [$this->createMock(TransferException::class)], // implémente GuzzleException
            [$this->createMock(Exception::class)],
        ];
    }

    /**
     * @dataProvider getSendRequestPossibleError
     * @expectedException \Import\Exception\CallException
     * @param Exception $ex
     */
    public function testSendRequestThrowsCallExceptionForAnyRequestError($ex)
    {
        $this->client->expects($this->once())->method('request')->willThrowException($ex);

        $service = new CallService();
        $service->setClient($this->client);
        $service->get('peu/importe');
    }

    /**
     * @expectedException \Import\Exception\CallException
     */
    public function testSendRequestThrowsCallExceptionWhenResponseStatusCodeIsNot200()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->exactly(2))->method('getStatusCode')->willReturn(123);

        $this->client->expects($this->once())->method('request')->willReturn($response);

        $service = new CallService();
        $service->setClient($this->client);
        $service->setLogger($this->logger);
        $service->get('peu/importe');
    }

    /**
     * @expectedException \Import\Exception\CallException
     */
    public function testSendRequestThrowsExceptionWhenResponseIsInvalidJSON()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::STATUS_CODE_200);
        $response->expects($this->once())->method('getBody')->willReturn('invalid JSON');

        $this->client->expects($this->once())->method('request')->willReturn($response);

        $service = new CallService();
        $service->setClient($this->client);
        $service->setLogger($this->logger);
        $service->get('peu/importe');
    }

    public function testCanGetVersion()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::STATUS_CODE_200);
        $response->expects($this->once())->method('getBody')->willReturn('{"valid": "JSON"}');

        $this->client->expects($this->once())->method('request')->with('GET', 'version/current')->willReturn($response);

        $service = new CallService();
        $service->setClient($this->client);
        $service->setLogger($this->logger);
        $service->getVersion();
    }

    public function testSendRequestReturnsJSON()
    {
        $response = $this->createMock(Response::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::STATUS_CODE_200);
        $response->expects($this->once())->method('getBody')->willReturn('{"valid": "JSON"}');

        $this->client->expects($this->once())->method('request')->willReturn($response);

        $service = new CallService();
        $service->setClient($this->client);
        $service->setLogger($this->logger);
        $json = $service->get('peu/importe');

        $entity = new stdClass();
        $entity->valid = "JSON";

        $this->assertEquals($entity, $json);
    }
}
