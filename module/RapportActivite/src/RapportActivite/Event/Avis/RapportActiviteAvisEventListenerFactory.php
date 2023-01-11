<?php

namespace RapportActivite\Event\Avis;

use Application\Service\Notification\NotifierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRule;
use RapportActivite\Rule\Validation\RapportActiviteValidationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;

class RapportActiviteAvisEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisEventListener
    {
        /** @var RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        /** @var \RapportActivite\Service\Validation\RapportActiviteValidationService $rapportActiviteValidationService */
        $rapportActiviteValidationService = $container->get(RapportActiviteValidationService::class);
        /** @var \Application\Service\Notification\NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);

        /** @var RapportActiviteValidationRule $rapportActiviteValidationRule */
        $rapportActiviteValidationRule = $container->get(RapportActiviteValidationRule::class);
        /** @var \RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRule $rapportActiviteNotificationRule */
        $rapportActiviteNotificationRule = $container->get(RapportActiviteAvisNotificationRule::class);

        $listener = new RapportActiviteAvisEventListener();
        $listener->setRapportActiviteAvisService($rapportActiviteAvisService);
        $listener->setRapportActiviteValidationService($rapportActiviteValidationService);
        $listener->setRapportActiviteValidationRule($rapportActiviteValidationRule);
        $listener->setRapportActiviteAvisNotificationRule($rapportActiviteNotificationRule);
        $listener->setApplicationNotifierService($notifierService);

        return $listener;
    }
}