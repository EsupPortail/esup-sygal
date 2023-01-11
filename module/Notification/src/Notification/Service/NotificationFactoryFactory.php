<?php

namespace Notification\Service;

use Interop\Container\ContainerInterface;
use Notification\Entity\Service\NotifEntityService;

/**
 * @author Unicaen
 */
class NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = NotificationFactory::class;

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NotificationFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var NotifEntityService $notifEntityService */
        $notifEntityService = $container->get(NotifEntityService::class);

        $class = $this->class;

        /** @var NotificationFactory $factory */
        $factory = new $class();
        $factory->setNotifEntityService($notifEntityService);

        return $factory;
    }
}
