<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;

class RapporteurControllerFactory{

    /**
     * @param ContainerInterface $container
     * @return RapporteurController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var EngagementImpartialiteService $engagementService
         * @var MembreService $membreService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurService::class);
        $avisService = $container->get(AvisService::class);
        $engagementService = $container->get(EngagementImpartialiteService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get(TheseService::class);

        $controller = new RapporteurController();
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setEngagementImpartialiteService($engagementService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        return $controller;
    }
}