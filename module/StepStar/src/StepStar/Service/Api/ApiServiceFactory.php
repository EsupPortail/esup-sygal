<?php

namespace StepStar\Service\Api;

use Interop\Container\ContainerInterface;
use StepStar\Config\ModuleConfig;
use StepStar\Service\Soap\SoapClient;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApiServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $config = null)
    {
        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        $config = $moduleConfig->getApiConfig();

        /** @var SoapClient $soapClient */
        $soapClient = $container->get(SoapClient::class);

        $service = new ApiService();
        $service->setSoapClient($soapClient);
        $service->setConfig($config);

        return $service;
    }
}