<?php

namespace Soutenance\Controller\Factory;

use Application\Service\These\TheseService;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
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
         * @var MembreService $membreService
         * @var TheseService $theseService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');

        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);

        return $controller;
    }
}