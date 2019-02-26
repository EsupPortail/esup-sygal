<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Service\Avis\AvisService;
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
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var UserContextService $userContextService
         * @var AvisService $avisService
         * @var ActeurService $acteurService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $avisService = $controllerManager->getServiceLocator()->get(AvisService::class);
        $acteurService = $controllerManager->getServiceLocator()->get(ActeurService::class);
        $userContextService = $controllerManager->getServiceLocator()->get('UserContextService');


        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierService($notifierService);
        $controller->setUserContextService($userContextService);
        $controller->setAvisService($avisService);
        $controller->setActeurService($acteurService);

        return $controller;
    }
}