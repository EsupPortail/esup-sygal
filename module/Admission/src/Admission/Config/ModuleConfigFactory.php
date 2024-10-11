<?php

namespace Admission\Config;

use Admission\Service\Transmission\TransmissionService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ModuleConfigFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ModuleConfig
    {
        $controller = new ModuleConfig();;
        $transmissionService = $container->get(TransmissionService::class);
        $controller->setTransmissionService($transmissionService);

        return $controller;
    }
}