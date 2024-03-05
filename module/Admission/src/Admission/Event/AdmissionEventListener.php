<?php

namespace Admission\Event;

use Admission\Service\Admission\AdmissionService;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Admission\Service\Notification\NotificationFactoryAwareTrait;
use Notification\Service\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Admission\Entity\Db\Admission;
use Webmozart\Assert\Assert;

class AdmissionEventListener implements ListenerAggregateInterface
{
    use UserContextServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use NotificationFactoryAwareTrait;

    use ListenerAggregateTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            AdmissionService::class,
            AdmissionService::ADMISSION__AJOUTE__EVENT,
            [$this, 'onRapportAjoute']
        );
        $events->getSharedManager()->attach(
            AdmissionService::class,
            AdmissionService::ADMISSION__SUPPRIME__EVENT,
            [$this, 'onRapportSupprime']
        );
    }

    public function onAdmissionAjoute(AdmissionEvent $event)
    {

    }

    public function onAdmissionSupprime(AdmissionEvent $event)
    {
        /** @var Admission $admission */
        $admission = $event->getTarget();

        Assert::isInstanceOf($admission, Admission::class);

        $this->handleNotification($admission, $event);
    }

    private function handleNotification(Admission $admission, AdmissionEvent $event)
    {
        $notif = $this->notificationFactory->createNotificationAdmissionSupprime($admission);
        $result = $this->notifierService->trigger($notif);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $event->setMessages(array_filter($messages));
    }
}