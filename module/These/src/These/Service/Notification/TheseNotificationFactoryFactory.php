<?php

namespace These\Service\Notification;

use Application\Service\Email\EmailTheseService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;

/**
 * @author Unicaen
 */
class TheseNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = TheseNotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TheseNotificationFactory
    {
        /** @var TheseNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /** @var EmailTheseService $emailTheseService */
        $emailTheseService =  $container->get(EmailTheseService::class);
        $factory->setEmailTheseService($emailTheseService);

        return $factory;
    }
}
