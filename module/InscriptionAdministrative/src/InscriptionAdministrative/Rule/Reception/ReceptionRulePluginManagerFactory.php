<?php

namespace InscriptionAdministrative\Rule\Reception;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ReceptionRulePluginManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReceptionRulePluginManager
    {
        return new ReceptionRulePluginManager($container);
    }
}