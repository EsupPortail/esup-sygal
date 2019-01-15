<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class PresoutenanceControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return  PresoutenanceController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var PropositionService $propositionService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var ActeurService $acteurService
         * @var ValidationService $validationService
         * @var RoleService $roleService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $acteurService = $controllerManager->getServiceLocator()->get(ActeurService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');

        /** @var PresoutenanceController $controller */
        $controller = new PresoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setIndividuService($individuService);
        $controller->setActeurService($acteurService);
        $controller->setNotifierService($notifierService);
        $controller->setValidationService($validationService);
        $controller->setRoleService($roleService);

        return $controller;
    }
}