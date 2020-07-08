<?php

namespace Application\Service\ListeDiffusion\Plugin;

use Application\Service\Individu\IndividuService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class ListeDiffusionRolePluginFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new ListeDiffusionRolePlugin();

        $config = $container->get('Config');
        $service->setConfig($config['sygal']['liste-diffusion'] ?? []);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get('IndividuService');
        $service->setIndividuService($individuService);

        return $service;
    }
}