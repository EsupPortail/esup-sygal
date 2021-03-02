<?php

namespace ComiteSuivi\Service\Notifier;

use Application\Service\Role\RoleService;
use Application\Service\Notification\NotificationFactory;
use Interop\Container\ContainerInterface;

class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory {

    protected $notifierServiceClass = NotifierService::class;

    /**
     * @param ContainerInterface $container
     * @return NotifierService|\Notification\Service\NotifierService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RoleService             $roleService
         */
        $roleService = $container->get('RoleService');

        /** @var NotifierService $service */
        $service = parent::__invoke($container);
        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setRoleService($roleService);

        return $service;
    }
}