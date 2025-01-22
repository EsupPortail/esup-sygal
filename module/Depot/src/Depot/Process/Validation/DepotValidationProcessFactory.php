<?php

namespace Depot\Process\Validation;

use Application\Service\UserContextService;
use Depot\Service\Notification\DepotNotificationFactory;
use Depot\Service\Validation\DepotValidationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;

class DepotValidationProcessFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DepotValidationProcess
    {
        $service = new DepotValidationProcess();

        /** @var UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $service->setUserContextService($userContextService);

        /**
         * @var NotifierService $notifierService
         * @var TheseService $theseService
         */
        $notifierService = $container->get(NotifierService::class);
        $theseService = $container->get(TheseService::class);
        $service->setNotifierService($notifierService);
        $service->setTheseService($theseService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $service->setDepotValidationService($depotValidationService);

        /** @var \Depot\Service\Notification\DepotNotificationFactory $depotNotificationFactory */
        $depotNotificationFactory = $container->get(DepotNotificationFactory::class);
        $service->setDepotNotificationFactory($depotNotificationFactory);


        return $service;
    }
}