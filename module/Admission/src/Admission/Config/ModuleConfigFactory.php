<?php

namespace Admission\Config;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModuleConfigFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ModuleConfig
    {
        return new ModuleConfig();
    }
}