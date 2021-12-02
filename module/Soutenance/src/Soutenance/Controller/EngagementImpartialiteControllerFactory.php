<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Evenement\EvenementService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Proposition\PropositionService;
use UnicaenAuthToken\Service\TokenService;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;

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
         * @var EvenementService $evenementService
         * @var PropositionService $propositionService
         * @var MembreService $membreService
         * @var NotifierSoutenanceService $notifierService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var TokenService $tokenService
         */
        $acteurService                  = $container->get(ActeurService::class);
        $evenementService               = $container->get(EvenementService::class);
        $propositionService             = $container->get(PropositionService::class);
        $membreService                  = $container->get(MembreService::class);
        $notifierService                = $container->get(NotifierSoutenanceService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);
        $tokenService                   = $container->get(TokenService::class);

        /** @var EngagementImpartialiteController $controller */
        $controller = new EngagementImpartialiteController();
        $controller->setActeurService($acteurService);
        $controller->setEvenementService($evenementService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setNotifierSoutenanceService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setTokenService($tokenService);

        return $controller;
    }
}