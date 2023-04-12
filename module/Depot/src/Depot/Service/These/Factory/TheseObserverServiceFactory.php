<?php

namespace Depot\Service\These\Factory;

use Notification\Service\NotifierService;
use Depot\Service\These\TheseObserverService;
use Psr\Container\ContainerInterface;
use These\Service\Notification\TheseNotificationFactory;
use These\Service\These\TheseService;

class TheseObserverServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseObserverService
    {
        /**
         * @var TheseService $theseService
         * @var NotifierService $notifierService
         */
        $theseService = $container->get('TheseService');
        $notifierService = $container->get(NotifierService::class);

        $service = new TheseObserverService();
        $service->setTheseService($theseService);
        $service->setNotifierService($notifierService);

        /** @var \These\Service\Notification\TheseNotificationFactory $theseNotificationFactory */
        $theseNotificationFactory = $container->get(TheseNotificationFactory::class);
        $service->setTheseNotificationFactory($theseNotificationFactory);

        return $service;
    }
}