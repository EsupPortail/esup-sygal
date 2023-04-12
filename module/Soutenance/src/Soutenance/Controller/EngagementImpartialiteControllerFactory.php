<?php

namespace Soutenance\Controller;

use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionService;
use These\Service\Acteur\ActeurService;
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
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var PropositionService $propositionService
         * @var RenduService $renduService
         */
        $acteurService                  = $container->get(ActeurService::class);
        $horodatageService              = $container->get(HorodatageService::class);
        $propositionService             = $container->get(PropositionService::class);
        $membreService                  = $container->get(MembreService::class);
        $notifierService                = $container->get(NotifierService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);
        $renduService                   = $container->get(RenduService::class);

        $controller = new EngagementImpartialiteController();
        $controller->setActeurService($acteurService);
        $controller->setHorodatageService($horodatageService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setNotifierService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setRenduService($renduService);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}