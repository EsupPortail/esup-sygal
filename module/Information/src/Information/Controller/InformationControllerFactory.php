<?php

namespace Information\Controller;

use Information\Controller\InformationController;
use Information\Service\InformationService;
use Zend\Mvc\Controller\ControllerManager;

class InformationControllerFactory {

    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var InformationService $infromationService
         */
        $infromationService = $controllerManager->getServiceLocator()->get(InformationService::class);

        $controller = new InformationController();
        $controller->setInformationService($infromationService);
        return $controller;
    }
}