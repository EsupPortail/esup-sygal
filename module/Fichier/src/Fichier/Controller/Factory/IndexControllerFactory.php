<?php

namespace Fichier\Controller\Factory;

use Fichier\Controller\IndexController;
use Psr\Container\ContainerInterface;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container): IndexController
    {
        $controller = new IndexController();

        return $controller;
    }
}