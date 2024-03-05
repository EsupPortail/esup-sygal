<?php

namespace Admission\Event\Operation;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Event\AdmissionEvent;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRuleAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Notification;
use Notification\Service\NotifierServiceAwareTrait;
use Webmozart\Assert\Assert;

abstract class AdmissionOperationAbstractEventListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    use OperationAttendueNotificationRuleAwareTrait;
    use AdmissionOperationRuleAwareTrait;

    use NotifierServiceAwareTrait;
    use NotificationFactoryAwareTrait;

    protected AdmissionOperationInterface $operationRealisee;
    protected AdmissionEvent $event;

    protected function initFromEvent(AdmissionEvent $event)
    {
        /** @var AdmissionOperationInterface $operationRealisee */
        $operationRealisee = $event->getTarget();
        Assert::isInstanceOf($operationRealisee, AdmissionOperationInterface::class);

        $this->operationRealisee = $operationRealisee;
        $this->event = $event;
    }

    /**
     * Notification des personnes concernées par l'opération suivante attendue.
     */
    protected function handleNotificationOperationAttendue()
    {
        // notif éventuelle
        $this->admissionOperationAttendueNotificationRule
            ->setOperationRealisee($this->operationRealisee)
            ->execute();
        if (! $this->admissionOperationAttendueNotificationRule->isNotificationRequired()) {
            return;
        }

        $notif = $this->notificationFactory->createNotificationOperationAttendue();
        $this->admissionOperationAttendueNotificationRule->configureNotification($notif);
        $notif = $this->notificationFactory->addOperationAttendueToTemplateOperationAttendue($notif->getOperationAttendue(), $notif);

        $result = $this->notifierService->trigger($notif);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $this->event->setMessages(array_filter($messages));
    }

    protected function triggerNotification(Notification $notification)
    {
        $result = $this->notifierService->trigger($notification);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $this->event->setMessages(array_filter($messages));
    }
}