<?php

namespace Application\Rule\PluginManager;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class RulePluginManagerFactory implements FactoryInterface
{
    protected string $configKey = 'rules';

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RulePluginManager
    {
        /** @var array $config */
        $config = $container->get('Config')[$this->configKey] ?? [];

        return new RulePluginManager($container, $config);
    }
}