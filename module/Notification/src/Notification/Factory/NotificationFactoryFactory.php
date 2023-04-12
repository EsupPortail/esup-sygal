<?php

namespace Notification\Factory;

use Notification\Entity\Service\NotifEntityService;
use Psr\Container\ContainerInterface;

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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotificationFactory
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
