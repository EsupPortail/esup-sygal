<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

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
         * @var TheseService $theseService
         * @var ValidationService $validationService
         * @var IndividuService $individuService
         * @var NotifierSoutenanceService $notifierService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         */
        $acteurService                  = $container->get(ActeurService::class);
        $propositionService             = $container->get(PropositionService::class);
        $membreService                  = $container->get(MembreService::class);
        $theseService                   = $container->get('TheseService');
        $validationService              = $container->get(ValidationService::class);
        $individuService                = $container->get('IndividuService');
        $notifierService                = $container->get(NotifierSoutenanceService::class);
        $engagementImpartialiteService  = $container->get(EngagementImpartialiteService::class);

        /** @var EngagementImpartialiteController $controller */
        $controller = new EngagementImpartialiteController();
        $controller->setActeurService($acteurService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierSoutenanceService($notifierService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);

        return $controller;
    }
}