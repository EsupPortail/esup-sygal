<?php

namespace UnicaenIdref\Controller;

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
        return new IndexController();
    }
}