<?php

namespace Soutenance\Controller\Factory;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Soutenance\Controller\SoutenanceController;
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
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $userContextService = $controllerManager->getServiceLocator()->get('UserContextService');


        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);

        return $controller;
    }
}