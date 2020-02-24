<?php

namespace ComiteSuivi\Service\Notifier;

use Application\Service\Role\RoleService;
use Application\Service\Notification\NotificationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory {

    protected $notifierServiceClass = NotifierService::class;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotifierService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var RoleService             $roleService
         */
        $roleService = $serviceLocator->get('RoleService');

        /** @var NotifierService $service */
        $service = parent::__invoke($serviceLocator);
        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $serviceLocator->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setRoleService($roleService);

        return $service;
    }
}