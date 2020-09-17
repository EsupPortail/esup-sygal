<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EngagementImpartialiteService $engagementService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         */
        $acteurService          = $container->get(ActeurService::class);
        $avisService            = $container->get(AvisService::class);
        $engagementService      = $container->get(EngagementImpartialiteService::class);
        $propositionService     = $container->get(PropositionService::class);
        $theseService           = $container->get('TheseService');
        $userContextService     = $container->get('UserContextService');

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