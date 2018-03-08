<?php

namespace Application\Controller\Factory;

use Application\Controller\StatistiqueController;
use Zend\Mvc\Controller\ControllerManager;

class StatistiqueControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return StatistiqueController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $controller = new StatistiqueController();
        return $controller;
    }
}