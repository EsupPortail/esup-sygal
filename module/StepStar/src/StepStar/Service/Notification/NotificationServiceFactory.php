<?php

namespace StepStar\Service\Notification;

use Application\Service\Notification\NotifierService;
use Psr\Container\ContainerInterface;

class NotificationServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotificationService
    {
        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);

        $service = new NotificationService();
        $service->setNotifierService($notifierService);

        return $service;
    }
}