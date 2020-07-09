<?php

namespace Application\Service\ListeDiffusion;

use Application\Service\Individu\IndividuService;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionRolePlugin;
use Application\Service\ListeDiffusion\Plugin\ListeDiffusionStructurePlugin;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class ListeDiffusionServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new ListeDiffusionService();

        $config = $container->get('Config');
        $service->setConfig($config['liste-diffusion'] ?? []);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get('IndividuService');
        $service->setIndividuService($individuService);

        $plugins = [
            $container->get(ListeDiffusionStructurePlugin::class),
            $container->get(ListeDiffusionRolePlugin::class),
        ];
        $service->setListeDiffusionServicePlugins($plugins);

        return $service;
    }
}