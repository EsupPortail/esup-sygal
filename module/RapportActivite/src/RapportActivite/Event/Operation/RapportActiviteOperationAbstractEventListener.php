<?php

namespace RapportActivite\Event\Operation;

use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Service\NotifierServiceAwareTrait;
use RapportActivite\Entity\RapportActiviteOperationInterface;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Rule\Operation\Notification\OperationAttendueNotificationRuleAwareTrait;
use RapportActivite\Rule\Operation\RapportActiviteOperationRuleAwareTrait;
use RapportActivite\Service\Notification\RapportActiviteNotificationFactoryAwareTrait;
use Webmozart\Assert\Assert;

abstract class RapportActiviteOperationAbstractEventListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    use OperationAttendueNotificationRuleAwareTrait;
    use RapportActiviteOperationRuleAwareTrait;

    use NotifierServiceAwareTrait;
    use RapportActiviteNotificationFactoryAwareTrait;

    protected RapportActiviteOperationInterface $operationRealisee;
    protected RapportActiviteEvent $event;

    protected function initFromEvent(RapportActiviteEvent $event)
    {
        /** @var \RapportActivite\Entity\RapportActiviteOperationInterface $operationRealisee */
        $operationRealisee = $event->getTarget();
        Assert::isInstanceOf($operationRealisee, RapportActiviteOperationInterface::class);

        $this->operationRealisee = $operationRealisee;
        $this->event = $event;
    }

    /**
     * Notification des personnes concernées par l'opération suivante attendue.
     */
    protected function handleNotificationOperationAttendue()
    {
        // notif éventuelle
        $this->rapportActiviteOperationAttendueNotificationRule
            ->setOperationRealisee($this->operationRealisee)
            ->execute();
        if (! $this->rapportActiviteOperationAttendueNotificationRule->isNotificationRequired()) {
            return;
        }

        $notif = $this->rapportActiviteNotificationFactory->createNotificationOperationAttendue();
        $this->rapportActiviteOperationAttendueNotificationRule->configureNotification($notif);

        $result = $this->notifierService->trigger($notif);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $this->event->setMessages(array_filter($messages));
    }
}