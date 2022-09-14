<?php

namespace These\Service\These\Factory;

use Application\Service\Notification\NotifierService;
use These\Service\These\TheseObserverService;
use These\Service\These\TheseService;
use Laminas\ServiceManager\ServiceManager;

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
        $service->setNotifierService($notifierService);

        return $service;
    }
}