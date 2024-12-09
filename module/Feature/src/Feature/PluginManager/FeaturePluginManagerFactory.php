<?php

namespace Feature\PluginManager;

use Feature\Entity\Db\Service\FeatureService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class FeaturePluginManagerFactory implements FactoryInterface
{
    private string $configKey = 'features';

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FeaturePluginManager
    {
        /** @var array $config */
        $config = $container->get('Config')[$this->configKey] ?? [];

        $service = new FeaturePluginManager($container, $config);

        /** @var FeatureService $featureService */
        $featureService = $container->get(FeatureService::class);
        $service->setFeatureService($featureService);

        return $service;
    }
}