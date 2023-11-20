<?php

namespace Application\Controller\Factory;

use Application\Controller\ConsoleController;
use Laminas\Log\Logger;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\Noop;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use UnicaenApp\Service\SQL\RunSQLService;

class ConsoleControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConsoleController
    {
        /** @var RunSQLService $runSQLService */
        $runSQLService = $container->get(RunSQLService::class);

        $controller = new ConsoleController();
        $controller->setLogger($this->createLogger());
        $controller->setContainer($container);
        $controller->setRunSQLService($runSQLService);

        return $controller;
    }

    private function createLogger(): LoggerInterface
    {
        $logger = new Logger();
        $logger->addWriter(new Noop());

        return $logger;
    }
}
