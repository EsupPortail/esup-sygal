<?php

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
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
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var TheseService $theseService
         */
        $theseService = $sl->get('TheseService');

        $controller = new IndexController();
        $controller->setTheseService($theseService);

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $sl->get('EtablissementService');
        $controller->setEtablissementService($etablissementService);

        /**
         * @var VariableService $variableService
         */
        $variableService = $sl->get('VariableService');
        $controller->setVariableService($variableService);

        return $controller;
    }
}
