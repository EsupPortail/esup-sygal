<?php

namespace Application\Controller\Factory;

use Application\Controller\DoctorantController;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\Variable\VariableService;
use Zend\Mvc\Controller\ControllerManager;

class DoctorantControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return DoctorantController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var VariableService $variableService
         * @var DoctorantService $doctorantService
         */
        $variableService = $controllerManager->getServiceLocator()->get('VariableService');
        $doctorantService = $controllerManager->getServiceLocator()->get('DoctorantService');

        $controller = new DoctorantController();
        $controller->setVariableService($variableService);
        $controller->setDoctorantService($doctorantService);

        return $controller;
    }
}
