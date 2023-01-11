<?php

namespace RapportActivite\Event\Avis;

use Application\Service\Notification\NotifierServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Exception\NotificationException;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Rule\Avis\RapportActiviteAvisNotificationRuleAwareTrait;
use RapportActivite\Rule\Validation\RapportActiviteValidationRuleAwareTrait;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class RapportActiviteAvisEventListener implements ListenerAggregateInterface
{
    use RapportActiviteValidationServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use NotifierServiceAwareTrait;

    use ListenerAggregateTrait;

    use RapportActiviteAvisNotificationRuleAwareTrait;
    use RapportActiviteValidationRuleAwareTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteAvisService::class,
            RapportActiviteAvisService::RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT,
            [$this, 'onAvisAjouteModifie']
        );
        $events->getSharedManager()->attach(
            RapportActiviteAvisService::class,
            RapportActiviteAvisService::RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT,
            [$this, 'onAvisAjouteModifie']
        );
    }

    public function onAvisAjouteModifie(RapportActiviteAvisEvent $event)
    {
        /** @var \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis */
        $rapportActiviteAvis = $event->getTarget();

        Assert::isInstanceOf($rapportActiviteAvis, RapportActiviteAvis::class);

        // L'ajout ou la modification d'un avis peut entrainer :
        //   - la création d'une validation ;
        //   - l'envoi d'une notification.
        $this->handleValidation($rapportActiviteAvis, $event);
        $this->handleNotification($rapportActiviteAvis, $event);
    }

    /**
     * Validation.
     *
     * L'ajout ou la modification d'un avis peut entrainer la création d'une validation.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @param \RapportActivite\Event\Avis\RapportActiviteAvisEvent $event
     */
    private function handleValidation(
        RapportActiviteAvis $rapportActiviteAvis,
        RapportActiviteAvisEvent $event): void
    {
        // une validation n'est créée que si la règle métier répond 'banco'
        $this->rapportActiviteValidationRule
            ->setRapportActivite($rapportActiviteAvis->getRapportActivite())
            ->execute();
        if (! $this->rapportActiviteValidationRule->isValidationPossible()) {
            return;
        }

        $rapportValidation = $this->rapportActiviteValidationService->newRapportValidation($rapportActiviteAvis->getRapportActivite());
        $newValidationEvent = $this->rapportActiviteValidationService->saveNewRapportValidation($rapportValidation);

        $event->setMessages([
            'success' => sprintf(
                "La validation du rapport '%s' a été enregistrée avec succès.",
                $rapportActiviteAvis->getRapportActivite()->getFichier()->getNom()
            ),
        ]);
        $event->addMessages($newValidationEvent->getMessages());
    }

    /**
     * Notification.
     *
     * L'ajout ou la modification d'un avis peut entrainer l'envoi d'une notification.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @param \RapportActivite\Event\Avis\RapportActiviteAvisEvent $event
     */
    private function handleNotification(
        RapportActiviteAvis $rapportActiviteAvis,
        RapportActiviteAvisEvent $event)
    {
        // notif éventuelle
        $this->rapportActiviteAvisNotificationRule
            ->setRapportActiviteAvis($rapportActiviteAvis)
            ->execute();
        if (! $this->rapportActiviteAvisNotificationRule->isNotificationRequired()) {
            return;
        }

        $notif = $this->rapportActiviteAvisService->newRapportActiviteAvisNotification($rapportActiviteAvis);
        $this->rapportActiviteAvisNotificationRule->configureNotification($notif);

        try {
            $this->applicationNotifierService->trigger($notif);
        } catch (NotificationException $e) {
            throw new RuntimeException("Impossible d'envoyer le mail de notification", null, $e);
        }

        $messages['info'] = ($notif->getInfoMessages()[0] ?? null);
        $messages['warning'] = ($notif->getWarningMessages()[0] ?? null);
        $event->setMessages(array_filter($messages));
    }
}