<?php

namespace Soutenance\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Service\Acteur\ActeurService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Evenement\EvenementService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierService;
use Soutenance\Service\Proposition\PropositionService;
use UnicaenAuthToken\Service\TokenService;
use UnicaenAuthToken\Service\TokenServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduService;

class EngagementImpartialiteControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : EngagementImpartialiteController
    {
        /**
         * @var ActeurService $acteurService
         * @var EvenementService $evenementService
         * @var PropositionService $propositionService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var RenduService $renduService
         * @var TokenService $tokenService
         */
        $acteurService                  = $container->get(ActeurService::class);
        $evenementService               = $container->get(EvenementService::class);
        $propositionService             = $container->get(PropositionService::class);
        $membreService                  = $container->get(MembreService::class);
        $notifierService                = $container->get(NotifierService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);
        $renduService                   = $container->get(RenduService::class);
        $tokenService                   = $container->get(TokenService::class);

        $controller = new EngagementImpartialiteController();
        $controller->setActeurService($acteurService);
        $controller->setEvenementService($evenementService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setSoutenanceNotifierService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setRenduService($renduService);
        $controller->setTokenService($tokenService);

        return $controller;
    }
}