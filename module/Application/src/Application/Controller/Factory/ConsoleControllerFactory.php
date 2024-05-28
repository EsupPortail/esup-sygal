<?php

namespace Application\Controller\Factory;

use Application\Controller\ConsoleController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ConsoleControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConsoleController
    {
        return new ConsoleController();
    }
}
