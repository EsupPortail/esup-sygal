<?php

namespace Application\Service\Notification;

use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
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

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');
        $factory->setUrlHelper($urlHelper);

        return $factory;
    }
}
