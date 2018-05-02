<?php

namespace Application\Controller\Factory;

use Application\Controller\ImportController;
use Application\Service\Notification\NotificationService;
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
         * @var TheseService $theseService
         * @var NotificationService $notificationService
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $notificationService = $controllerManager->getServiceLocator()->get(NotificationService::class);

        $controller = new ImportController();
        $controller->setTheseService($theseService);
        $controller->setNotificationService($notificationService);

        return $controller;
    }
}


