<?php

namespace Depot\Controller\Factory;

use Depot\Controller\ConsoleController;
use Depot\Service\These\DepotService;
use Interop\Container\ContainerInterface;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\Stream;
use These\Service\These\TheseService;

class ConsoleControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return ConsoleController
     */
    public function __invoke(ContainerInterface $container): ConsoleController
    {
        /**
         * @var TheseService $theseService
         */
        $theseService = $container->get('TheseService');

        $controller = new ConsoleController();
        $controller->setTheseService($theseService);
        $controller->setLogger($this->createLogger());

        /** @var DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $controller->setDepotService($depotService);

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