<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use HDR\Service\HDRService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use These\Service\These\TheseService;
use UnicaenRenderer\Service\Rendu\RenduService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

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
         * @var ActeurTheseService $acteurService
         * @var ActeurHDRService $acteurHDRService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var RenduService $renduService
         * @var PropositionTheseService  $propositionTheseService
         * @var PropositionHDRService $propositionHDRService
         * @var HDRService $hdrService
         * @var TheseService $theseService
         */
        $acteurTheseService          = $container->get(ActeurTheseService::class);
        $acteurHDRService          = $container->get(ActeurHDRService::class);
        $horodatageService              = $container->get(HorodatageService::class);
        $membreService                  = $container->get(MembreService::class);
        $notifierService                = $container->get(NotifierService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);
        $renduService                   = $container->get(RenduService::class);
        $theseService = $container->get(TheseService::class);
        $hdrService = $container->get(HDRService::class);
        $propositionTheseService = $container->get(PropositionTheseService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);

        $controller = new EngagementImpartialiteController();
        $controller->setActeurTheseService($acteurTheseService);
        $controller->setActeurHDRService($acteurHDRService);
        $controller->setHorodatageService($horodatageService);
        $controller->setMembreService($membreService);
        $controller->setNotifierService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setRenduService($renduService);
        $controller->setPropositionTheseService($propositionTheseService);
        $controller->setPropositionHDRService($propositionHDRService);
        $controller->setTheseService($theseService);
        $controller->setHDRService($hdrService);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $controller->setTemplateVariablePluginManager($rapm);

        return $controller;
    }
}