<?php

namespace Import\Controller\Factory;

use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Import\Controller\SynchroController;
use Import\Service\SynchroService;
use Zend\Mvc\Controller\ControllerManager;

class SynchroControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return SynchroController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var TheseService $theseService
         * @var NotifierService $notifierService
         */
        $theseService = $sl->get('TheseService');
        $notifierService = $sl->get(NotifierService::class);

        /** @var SynchroService $synchroService */
        $synchroService = $sl->get(SynchroService::class);

        $controller = new SynchroController();
        $controller->setSynchroService($synchroService);
        $controller->setTheseService($theseService);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}


