<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class EngagementImpartialiteControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return EngagementImpartialiteController
     */
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var PropositionService $propositionService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);

        /** @var EngagementImpartialiteController $controller */
        $controller = new EngagementImpartialiteController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}