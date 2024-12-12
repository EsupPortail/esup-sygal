<?php

namespace Application\Renderer\Template\Variable\PluginManager;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TemplateVariablePluginManagerFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TemplateVariablePluginManager
    {
        /** @var array $config */
        $config = $container->get('Config');
        $templateVariableConfig = $config['renderer']['template_variables'] ?? [];

        return new TemplateVariablePluginManager($container, [
            'factories' => $templateVariableConfig['factories'] ?? [],
            'aliases' => $templateVariableConfig['aliases'] ?? [],
            'shared' => $templateVariableConfig['shared'] ?? [],
        ]);
    }
}