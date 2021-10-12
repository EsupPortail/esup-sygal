<?php

namespace Formation\Service\Notification;

use Application\Service\Notification\NotificationFactory;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierServiceFactory;
use Laminas\View\Helper\Url as UrlHelper;

class NotificationServiceFactory extends NotifierServiceFactory
{

    protected $notifierServiceClass = NotificationService::class;

    /**
     * @param ContainerInterface $container
     * @return NotificationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var NotificationService $service */
        $service = parent::__invoke($container);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setUrlHelper($urlHelper);

        return $service;
    }
}