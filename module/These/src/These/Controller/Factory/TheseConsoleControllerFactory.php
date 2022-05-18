<?php

namespace These\Controller\Factory;

use These\Controller\TheseConsoleController;
use These\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\Stream;

class TheseConsoleControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseConsoleController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get('TheseService');

        $controller = new TheseConsoleController();
        $controller->setTheseService($theseService);
        $controller->setLogger($this->createLogger());

        return $controller;
    }

    /**
     * @return LoggerInterface
     */
    private function createLogger()
    {
        $logger = new Logger();
        $logger->addWriter(new Stream('php://output'));

        return $logger;
    }
}