<?php

namespace StepStar\Service\Api;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use StepStar\Service\Soap\SoapClient;

class ApiServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ApiService
    {
        /** @var array $config */
        $config = $container->get('Config');

        $operations = $config['step_star']['api']['operations'];
        $params = $config['step_star']['api']['params'];

        /** @var SoapClient $soapClient */
        $soapClient = $container->get(SoapClient::class);

        $service = new ApiService();
        $service->setSoapClient($soapClient);
        $service->setOperations($operations);
        $service->setParams($params);

        return $service;
    }
}