<?php

namespace Depot\Service\These\Factory;

use Application\Service\Notification\NotifierService;
use Depot\Service\These\TheseObserverService;
use Laminas\ServiceManager\ServiceManager;
use These\Service\These\TheseService;

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
         * @var TheseService $theseService
         * @var NotifierService $notifierService
         */
        $theseService = $serviveManager->get('TheseService');
        $notifierService = $serviveManager->get(NotifierService::class);

        $service = new TheseObserverService();
        $service->setTheseService($theseService);
        $service->setApplicationNotifierService($notifierService);

        return $service;
    }
}