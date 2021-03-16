<?php

namespace StepStar\Service\Soap;

use Interop\Container\ContainerInterface;
use StepStar\Config\ModuleConfig;

class SoapClientFactory
{
    public function __invoke(ContainerInterface $container): SoapClient
    {
        /** @var \StepStar\Config\ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);

        $soapClientConfig = $moduleConfig->getSoapClientConfig();

        return new SoapClient($soapClientConfig);
    }
}