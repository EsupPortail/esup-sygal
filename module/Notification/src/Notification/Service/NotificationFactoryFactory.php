<?php

namespace Notification\Service;

use Notification\Entity\Service\NotifEntityService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Unicaen
 */
class NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected $class = NotificationFactory::class;

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationFactory
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotifEntityService $notifEntityService */
        $notifEntityService = $serviceLocator->get(NotifEntityService::class);

        $class = $this->class;

        /** @var NotificationFactory $factory */
        $factory = new $class();
        $factory->setNotifEntityService($notifEntityService);

        return $factory;
    }
}
