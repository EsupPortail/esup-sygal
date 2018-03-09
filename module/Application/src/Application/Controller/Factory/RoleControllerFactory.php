<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Zend\Mvc\Controller\ControllerManager;

class RoleControllerFactory
{

    public function __invoke(ControllerManager $controllerManager)
    {

        $controller = new RoleController();

        return $controller;
    }
}