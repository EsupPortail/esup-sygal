<?php

namespace Formation\Controller;

use Interop\Container\ContainerInterface;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
    {
        $controller = new IndexController();
        return $controller;
    }

}