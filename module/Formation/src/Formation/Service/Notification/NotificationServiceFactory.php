<?php

namespace Formation\Service\Notification;

use Application\Service\Notification\NotificationFactory;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierServiceFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenRenderer\Service\Rendu\RenduService;

class NotificationServiceFactory extends NotifierServiceFactory
{

    protected $notifierServiceClass = NotificationService::class;

    /**
     * @param ContainerInterface $container
     * @return NotificationService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : NotificationService
    {
        /**
         * @var NotificationService $service
         * @var RenduService $renduService
         */
        $service = parent::__invoke($container);
        $renduService = $container->get(RenduService::class);

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setRenduService($renduService);

        return $service;
    }
}