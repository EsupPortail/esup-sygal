<?php

namespace Admission\Event\Validation;

use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRule;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Operation\AdmissionOperationService;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionValidationEventListenerFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionValidationEventListener
    {
        $listener = new AdmissionValidationEventListener();

        /** @var AdmissionOperationService $admissionOperationService */
        $admissionOperationService = $container->get(AdmissionOperationService::class);
        $listener->setAdmissionOperationService($admissionOperationService);

        /** @var NotificationFactory $admissionNotificationFactory */
        $admissionNotificationFactory = $container->get(NotificationFactory::class);
        $listener->setNotificationFactory($admissionNotificationFactory);

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        /** @var AdmissionOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(AdmissionOperationRule::class);
        $listener->setAdmissionOperationRule($rapportActiviteOperationRule);

        /** @var OperationAttendueNotificationRule $operationAttendueNotificationRule */
        $operationAttendueNotificationRule = $container->get(OperationAttendueNotificationRule::class);
        $listener->setAdmissionOperationAttendueNotificationRule($operationAttendueNotificationRule);

        return $listener;
    }
}