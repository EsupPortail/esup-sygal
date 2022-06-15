<?php

namespace RapportActivite\Event;

use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\UserContextServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Exception\NotificationException;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\RapportActiviteServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class RapportActiviteEventListener implements ListenerAggregateInterface
{
    use UserContextServiceAwareTrait;
    use RapportActiviteServiceAwareTrait;
    use NotifierServiceAwareTrait;

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

        $notif = $this->rapportActiviteService->newRapportActiviteSupprimeNotification($rapportActivite);
        try {
            $this->notifierService->trigger($notif);
        } catch (NotificationException $e) {
            throw new RuntimeException("Impossible d'envoyer le mail de notification", null, $e);
        }

        $messages['info'] = ($notif->getInfoMessages()[0] ?? null);
        $messages['warning'] = ($notif->getWarningMessages()[0] ?? null);
        $event->setMessages(array_filter($messages));
    }
}