<?php

namespace StepStar\Controller\Log;

use Psr\Container\ContainerInterface;
use StepStar\Service\Log\LogService;

class LogControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogController
    {
        $controller = new LogController();

        /** @var \StepStar\Service\Log\LogService $logService */
        $logService = $container->get(LogService::class);
        $controller->setLogService($logService);

        return $controller;
    }
}