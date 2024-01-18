<?php

namespace Admission\Event\Avis;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\Notification\NotificationFactory;
use Notification\Service\NotifierService;
use Psr\Container\ContainerInterface;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRule;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Operation\AdmissionOperationService;

class AdmissionAvisEventListenerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionAvisEventListener
    {
        $listener = new AdmissionAvisEventListener();

        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $listener->setEntityManager($entityManager);

        /** @var AdmissionOperationService $admissionOperationService */
        $admissionOperationService = $container->get(AdmissionOperationService::class);
        $listener->setAdmissionOperationService($admissionOperationService);

        /** @var AdmissionOperationRule $admissionOperationRule */
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);
        $listener->setAdmissionOperationRule($admissionOperationRule);

        /** @var OperationAttendueNotificationRule $operationAttendueNotificationRule */
        $operationAttendueNotificationRule = $container->get(OperationAttendueNotificationRule::class);
        $listener->setAdmissionOperationAttendueNotificationRule($operationAttendueNotificationRule);

        /** @var NotificationFactory $admissionNotificationFactory */
        $admissionNotificationFactory = $container->get(NotificationFactory::class);
        $listener->setNotificationFactory($admissionNotificationFactory);

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $listener->setNotifierService($notifierService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $listener->setAdmissionService($admissionService);

        return $listener;
    }
}