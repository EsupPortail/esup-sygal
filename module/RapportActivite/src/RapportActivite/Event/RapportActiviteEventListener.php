<?php

namespace RapportActivite\Event;

use Notification\Service\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Service\Notification\RapportActiviteNotificationFactoryAwareTrait;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use Webmozart\Assert\Assert;

class RapportActiviteEventListener implements ListenerAggregateInterface
{
    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use RapportActiviteNotificationFactoryAwareTrait;

    use ListenerAggregateTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteService::class,
            RapportActiviteService::RAPPORT_ACTIVITE__AJOUTE__EVENT,
            [$this, 'onRapportAjoute']
        );
        $events->getSharedManager()->attach(
            RapportActiviteService::class,
            RapportActiviteService::RAPPORT_ACTIVITE__SUPPRIME__EVENT,
            [$this, 'onRapportSupprime']
        );
    }

    public function onRapportAjoute(RapportActiviteEvent $event)
    {

    }

    public function onRapportSupprime(RapportActiviteEvent $event)
    {
        /** @var \RapportActivite\Entity\Db\RapportActivite $rapportActivite */
        $rapportActivite = $event->getTarget();

        Assert::isInstanceOf($rapportActivite, RapportActivite::class);

        $this->handleNotification($rapportActivite, $event);
    }

    private function handleNotification(RapportActivite $rapportActivite, RapportActiviteEvent $event)
    {
        // Pas de notif si c'est le doctorant lui-même qui a supprimé son rapport
        if (($doctorant = $this->userContextService->getIdentityDoctorant()) &&
            $doctorant === $rapportActivite->getThese()->getDoctorant()) {
            return;
        }

        $notif = $this->rapportActiviteNotificationFactory->createNotificationRapportActiviteSupprime($rapportActivite);
        $result = $this->notifierService->trigger($notif);

        $messages['info'] = ($result->getSuccessMessages()[0] ?? null);
        $messages['warning'] = ($result->getErrorMessages()[0] ?? null);
        $event->setMessages(array_filter($messages));
    }
}