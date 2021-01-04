<?php

namespace UnicaenDeploy\Service;

use Interop\Container\ContainerInterface;
use UnicaenDeploy\Config\Config;
use Zend\ServiceManager\Factory\FactoryInterface;

class DeployServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Config $config */
        $config = $container->get(Config::class);

        $service = new DeployService();
        $service->setConfig($config);

        return $service;
    }
}