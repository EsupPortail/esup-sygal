<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\Doublon\DoublonService;

class DoublonControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : DoublonController
    {
        $controller = new DoublonController();

        /** @var \Substitution\Service\Doublon\DoublonService $doublonService */
        $doublonService = $container->get(DoublonService::class);
        $controller->setDoublonService($doublonService);

        return $controller;
    }
}