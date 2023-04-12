<?php

namespace RapportActivite\Event\Validation;

use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Operation\Notification\OperationAttendueNotificationRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Notification\RapportActiviteNotificationFactory;
use RapportActivite\Service\Operation\RapportActiviteOperationService;

class RapportActiviteValidationEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteValidationEventListener
    {
        $listener = new RapportActiviteValidationEventListener();

        /** @var \RapportActivite\Service\Operation\RapportActiviteOperationService $rapportActiviteOperationService */
        $rapportActiviteOperationService = $container->get(RapportActiviteOperationService::class);
        $listener->setRapportActiviteOperationService($rapportActiviteOperationService);

        /** @var RapportActiviteNotificationFactory $rapportActiviteNotificationFactory */
        $rapportActiviteNotificationFactory = $container->get(RapportActiviteNotificationFactory::class);
        $listener->setRapportActiviteNotificationFactory($rapportActiviteNotificationFactory);

        /** @var \Notification\Service\NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $listener->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \RapportActivite\Rule\Operation\Notification\OperationAttendueNotificationRule $operationAttendueNotificationRule */
        $operationAttendueNotificationRule = $container->get(OperationAttendueNotificationRule::class);
        $listener->setRapportActiviteOperationAttendueNotificationRule($operationAttendueNotificationRule);

        return $listener;
    }
}