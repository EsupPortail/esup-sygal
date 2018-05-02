<?php

namespace Application\Service\These\Factory;

use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseObserverService;
use Application\Service\These\TheseService;
use Zend\ServiceManager\ServiceManager;

class TheseObserverServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceManager $serviveManager
     * @return TheseObserverService
     */
    public function __invoke(ServiceManager $serviveManager)
    {
        /**
         * @var TheseService    $theseService
         * @var NotifierService $notificationService
         */
        $theseService = $serviveManager->get('TheseService');
        $notificationService = $serviveManager->get(NotifierService::class);

        $service = new TheseObserverService();
        $service->setTheseService($theseService);
        $service->setNotificationService($notificationService);


        return $service;
    }
}