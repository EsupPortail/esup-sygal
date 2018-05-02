<?php

namespace Application\Controller\Factory;

use Application\Controller\ImportController;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Zend\Mvc\Controller\ControllerManager;


class ImportControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return ImportController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var TheseService    $theseService
         * @var NotifierService $notificationService
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $notificationService = $controllerManager->getServiceLocator()->get(NotifierService::class);

        $controller = new ImportController();
        $controller->setTheseService($theseService);
        $controller->setNotificationService($notificationService);

        return $controller;
    }
}


