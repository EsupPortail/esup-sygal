<?php

namespace Application\Service\Notification;

use Application\Service\Url\UrlService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;

/**
 * @author Unicaen
 */
class ApplicationNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = ApplicationNotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ApplicationNotificationFactory
    {
        /** @var ApplicationNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /** @var UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $factory->setUrlService($urlService);

        return $factory;
    }
}
