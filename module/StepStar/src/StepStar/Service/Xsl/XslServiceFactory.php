<?php

namespace StepStar\Service\Xsl;

use Interop\Container\ContainerInterface;
use StepStar\Config\ModuleConfig;
use Zend\ServiceManager\Factory\FactoryInterface;

class XslServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $config = null)
    {
        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        $config = $moduleConfig->getXslConfig();

        $service = new XslService();
        $service->setConfig($config);

        return $service;
    }
}