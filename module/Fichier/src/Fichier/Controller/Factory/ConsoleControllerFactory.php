<?php

namespace Fichier\Controller\Factory;

use Fichier\Controller\ConsoleController;
use Psr\Container\ContainerInterface;

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
        $controller = new ConsoleController();

        return $controller;
    }
}