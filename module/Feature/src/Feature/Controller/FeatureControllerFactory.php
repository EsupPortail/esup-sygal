<?php

namespace Feature\Controller;

use Psr\Container\ContainerInterface;

class FeatureControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FeatureController
    {
        $service = new FeatureController();

        return $service;
    }
}