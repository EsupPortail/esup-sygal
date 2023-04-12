<?php

namespace RapportActivite\Event\Validation;

use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;

class RapportActiviteValidationEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationEventListener
    {
        $listener = new RapportActiviteValidationEventListener();

        /** @var \RapportActivite\Service\Avis\RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $listener->setRapportActiviteAvisService($rapportActiviteAvisService);

        /** @var RapportActiviteValidationService $rapportActiviteValidationService */
        $rapportActiviteValidationService = $container->get(RapportActiviteValidationService::class);
        $listener->setRapportActiviteValidationService($rapportActiviteValidationService);

        /** @var \Notification\Service\NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        return $listener;
    }
}