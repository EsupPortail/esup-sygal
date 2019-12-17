<?php

namespace Soutenance\Controller\EngagementImpartialite;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Application\Service\These\TheseService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

class EngagementImpartialiteControllerFactory
{
    /**
     * @param ControllerManager $manager
     * @return EngagementImpartialiteController
     */
    public function __invoke(ControllerManager $manager)
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
        $acteurService                  = $manager->getServiceLocator()->get(ActeurService::class);
        $propositionService             = $manager->getServiceLocator()->get(PropositionService::class);
        $membreService                  = $manager->getServiceLocator()->get(MembreService::class);
        $theseService                   = $manager->getServiceLocator()->get('TheseService');
        $validationService              = $manager->getServiceLocator()->get(ValidationService::class);
        $individuService                = $manager->getServiceLocator()->get('IndividuService');
        $notifierService                = $manager->getServiceLocator()->get(NotifierSoutenanceService::class);
        $engagementImpartialiteService  = $manager->getServiceLocator()->get(EngagementImpartialiteService::class);

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