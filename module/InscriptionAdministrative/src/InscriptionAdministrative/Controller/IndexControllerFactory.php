<?php

namespace InscriptionAdministrative\Controller;

use Psr\Container\ContainerInterface;

class IndexControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndexController
    {
        $controller = new IndexController();



        return $controller;
    }
}