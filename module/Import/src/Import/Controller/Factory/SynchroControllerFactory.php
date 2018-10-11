<?php

namespace Import\Controller\Factory;

use Import\Controller\SynchroController;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
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
        /**
         * @var TheseService $theseService
         * @var NotifierService $notifierService
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);

        $controller = new SynchroController();
        $controller->setTheseService($theseService);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}


