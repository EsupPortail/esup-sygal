<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\Log\LogService;

class LogControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogController
    {
        $controller = new LogController;

        /** @var \Substitution\Service\Log\LogService $service */
        $service = $container->get(LogService::class);
        $controller->setLogService($service);

        return $controller;
    }
}