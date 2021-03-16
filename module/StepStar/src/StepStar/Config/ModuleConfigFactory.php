<?php

namespace StepStar\Config;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ModuleConfigFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     * @return ModuleConfig
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ModuleConfig
    {
        /** @var array $config */
        $config = $container->get('Config');

        return new ModuleConfig($config['step_star']);
    }
}