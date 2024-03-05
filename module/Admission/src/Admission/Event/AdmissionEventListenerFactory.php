<?php

namespace Admission\Event;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\Notification\NotificationFactory;
use Application\Service\UserContextService;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionEventListenerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionEventListener
    {
        $listener = new AdmissionEventListener();

        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $listener->setUserContextService($userContextService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $listener->setAdmissionService($admissionService);

        /** @var NotificationFactory $admissionNotificationFactory */
        $admissionNotificationFactory = $container->get(NotificationFactory::class);
        $listener->setNotificationFactory($admissionNotificationFactory);

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        return $listener;
    }
}