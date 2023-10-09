<?php

namespace Admission\Service\Notification;

use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenRenderer\Service\Rendu\RenduService;
use Notification\Factory\NotificationFactoryFactory as NFF;

/**
 * @author Unicaen
 */
class NotificationFactoryFactory extends NFF
{
    /**
     * @var string
     */
    protected string $class = NotificationFactory::class;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotificationFactory
    {
        /** @var NotificationFactory $factory */
        $factory = parent::__invoke($container);

        /**
         * @var RenduService $renduService
         * @var UserContextService $userContextService
         */
        $renduService = $container->get(RenduService::class);
        $userContextService = $container->get(UserContextService::class);
        $factory->setRenduService($renduService);
        $factory->setUserContextService($userContextService);

        return $factory;
    }
}
