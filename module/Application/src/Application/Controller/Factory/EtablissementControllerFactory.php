<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Zend\Mvc\Controller\ControllerManager;

class EtablissementControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EtablissementController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $controller = new EtablissementController();

        return $controller;
    }
}