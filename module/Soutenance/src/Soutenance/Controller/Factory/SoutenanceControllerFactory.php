<?php

namespace Soutenance\Controller\Factory;

use Application\Service\These\TheseService;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class SoutenanceControllerFactory
{
    /**
     * @param ControllerManager $manager
     * @return SoutenanceController
     */
    public function __invoke(ControllerManager $manager)
    {

        /**
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         */
        $propositionService     = $manager->getServiceLocator()->get(PropositionService::class);
        $theseService           = $manager->getServiceLocator()->get('TheseService');


        /** @var SoutenanceController $controller */
        $controller = new SoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);

        return $controller;
    }
}