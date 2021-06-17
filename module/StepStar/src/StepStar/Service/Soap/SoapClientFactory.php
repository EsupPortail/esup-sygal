<?php

namespace StepStar\Service\Soap;

use Interop\Container\ContainerInterface;

class SoapClientFactory
{
    public function __invoke(ContainerInterface $container): SoapClient
    {
        /** @var array $config */
        $config = $container->get('Config');

        $soapClientConfig = $config['step_star']['api']['soap_client'];

        return new SoapClient($soapClientConfig);
    }
}