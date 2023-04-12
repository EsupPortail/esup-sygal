<?php

namespace RapportActivite\Event;

use Notification\Service\NotifierService;
use Application\Service\UserContextService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteEventListener
    {
        $listener = new RapportActiviteEventListener();

        /** @var \Application\Service\UserContextService $userContextService */
        $userContextService = $container->get(UserContextService::class);
        $listener->setUserContextService($userContextService);

        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $listener->setRapportActiviteService($rapportActiviteService);

        /** @var \Notification\Service\NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        return $listener;
    }
}