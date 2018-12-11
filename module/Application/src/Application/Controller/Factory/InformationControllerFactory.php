<?php

namespace Application\Controller\Factory;

use Application\Controller\InformationController;
use Application\Service\Information\InformationService;
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