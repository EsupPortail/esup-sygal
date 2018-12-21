<?php

namespace Information\Controller;

use Zend\Mvc\Controller\ControllerManager;

class IndexControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return IndexController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $controller = new IndexController();

        return $controller;
    }

}