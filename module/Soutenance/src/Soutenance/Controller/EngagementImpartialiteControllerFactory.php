<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Proposition\PropositionService;

class EngagementImpartialiteControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var PropositionService $propositionService
         * @var MembreService $membreService
         * @var NotifierSoutenanceService $notifierService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         */
        $acteurService                  = $container->get(ActeurService::class);
        $propositionService             = $container->get(PropositionService::class);
        $membreService                  = $container->get(MembreService::class);
        $notifierService                = $container->get(NotifierSoutenanceService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);

        /** @var EngagementImpartialiteController $controller */
        $controller = new EngagementImpartialiteController();
        $controller->setActeurService($acteurService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setNotifierSoutenanceService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);

        return $controller;
    }
}