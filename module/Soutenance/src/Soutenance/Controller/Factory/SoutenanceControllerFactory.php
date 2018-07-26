<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
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
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);

        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}