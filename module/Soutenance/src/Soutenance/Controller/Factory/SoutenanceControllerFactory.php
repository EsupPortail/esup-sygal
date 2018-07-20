<?php

namespace Soutenance\Controller\Factory;

use Application\Service\These\TheseService;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Service\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class SoutenanceControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return SoutenanceController
     */
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');

        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);

        return $controller;
    }
}