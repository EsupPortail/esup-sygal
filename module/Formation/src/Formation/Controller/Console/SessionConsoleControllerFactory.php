<?php

namespace Formation\Controller\Console;

use Formation\Service\Session\SessionService;
use Psr\Container\ContainerInterface;

class SessionConsoleControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SessionConsoleController
    {
        $controller = new SessionConsoleController();

        /** @var SessionService $sessionService */
        $sessionService = $container->get(SessionService::class);
        $controller->setSessionService($sessionService);

        return $controller;
    }
}