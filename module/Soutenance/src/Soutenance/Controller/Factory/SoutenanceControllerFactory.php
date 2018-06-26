<?php

namespace Soutenance\Controller\Factory;

use Soutenance\Controller\SoutenanceController;
use Zend\Mvc\Controller\ControllerManager;

class SoutenanceControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return SoutenanceController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $controller = new SoutenanceController();

        return $controller;
    }
}