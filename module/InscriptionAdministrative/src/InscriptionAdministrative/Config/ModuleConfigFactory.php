<?php

namespace InscriptionAdministrative\Config;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModuleConfigFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ModuleConfig
    {
        $config = $container->get('config')['sygal']['inscription_administrative'];

        return new ModuleConfig($config);
    }
}