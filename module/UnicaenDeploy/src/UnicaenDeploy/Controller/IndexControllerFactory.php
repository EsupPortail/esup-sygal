<?php

namespace UnicaenDeploy\Controller;

use Interop\Container\ContainerInterface;
use UnicaenDeploy\Service\DeployService;
use Zend\ServiceManager\Factory\FactoryInterface;

class IndexControllerFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var DeployService $deployService */
        $deployService = $container->get(DeployService::class);

        $controller = new IndexController();
        $controller->setDeployService($deployService);

        return $controller;
    }
}