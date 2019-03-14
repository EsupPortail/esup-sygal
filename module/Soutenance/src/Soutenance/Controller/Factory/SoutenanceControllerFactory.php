<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class SoutenanceControllerFactory
{
    /**
     * @param ControllerManager $manager
     * @return SoutenanceController
     */
    public function __invoke(ControllerManager $manager)
    {

        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EngagementImpartialiteService $engagementService
         * @var MembreService $membreService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         */
        $acteurService          = $manager->getServiceLocator()->get(ActeurService::class);
        $avisService            = $manager->getServiceLocator()->get(AvisService::class);
        $engagementService      = $manager->getServiceLocator()->get(EngagementImpartialiteService::class);
        $membreService          = $manager->getServiceLocator()->get(MembreService::class);
        $propositionService     = $manager->getServiceLocator()->get(PropositionService::class);
        $theseService           = $manager->getServiceLocator()->get('TheseService');
        $userContextService     = $manager->getServiceLocator()->get('UserContextService');


        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setMembreService($membreService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);

        return $controller;
    }
}