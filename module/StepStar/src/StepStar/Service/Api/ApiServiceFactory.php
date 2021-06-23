<?php

namespace StepStar\Service\Api;

use Interop\Container\ContainerInterface;
use StepStar\Service\Soap\SoapClient;
use Zend\ServiceManager\Factory\FactoryInterface;

class ApiServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var array $config */
        $config = $container->get('Config');

        $params = $config['step_star']['api']['params'];

        /** @var SoapClient $soapClient */
        $soapClient = $container->get(SoapClient::class);

        $service = new ApiService();
        $service->setSoapClient($soapClient);
        $service->setParams($params);

        return $service;
    }
}