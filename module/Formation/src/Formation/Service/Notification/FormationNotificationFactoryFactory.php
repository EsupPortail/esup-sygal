<?php

namespace Formation\Service\Notification;

use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;
use UnicaenRenderer\Service\Rendu\RenduService;

/**
 * @author Unicaen
 */
class FormationNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = FormationNotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationNotificationFactory
    {
        /** @var FormationNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /** @var RenduService $renduService */
        $renduService = $container->get(RenduService::class);
        $factory->setRenduService($renduService);

        return $factory;
    }
}
