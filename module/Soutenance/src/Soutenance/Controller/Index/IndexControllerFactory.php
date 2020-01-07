<?php

namespace Soutenance\Controller\Index;

use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class IndexControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EngagementImpartialiteService $engagementService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         */
        $acteurService          = $manager->getServiceLocator()->get(ActeurService::class);
        $avisService            = $manager->getServiceLocator()->get(AvisService::class);
        $engagementService      = $manager->getServiceLocator()->get(EngagementImpartialiteService::class);
        $propositionService     = $manager->getServiceLocator()->get(PropositionService::class);
        $theseService           = $manager->getServiceLocator()->get('TheseService');
        $userContextService     = $manager->getServiceLocator()->get('UserContextService');


        /** @var IndexController $controller */
        $controller = new IndexController();
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);

        return $controller;
    }
}