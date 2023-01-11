<?php

namespace RapportActivite\Event\Validation;

use Application\Service\Notification\NotifierServiceAwareTrait;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Notification\Exception\NotificationException;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use RapportActivite\Service\Validation\RapportActiviteValidationServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;
use Webmozart\Assert\Assert;

class RapportActiviteValidationEventListener implements ListenerAggregateInterface
{
    use RapportActiviteValidationServiceAwareTrait;
    use RapportActiviteAvisServiceAwareTrait;
    use NotifierServiceAwareTrait;

    use ListenerAggregateTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteValidationService::class,
            RapportActiviteValidationService::RAPPORT_ACTIVITE__VALIDATION_AJOUTEE__EVENT,
            [$this, 'onValidationAjoutee']
        );
        $events->getSharedManager()->attach(
            RapportActiviteValidationService::class,
            RapportActiviteValidationService::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
            [$this, 'onValidationSupprimee']
        );
    }

    /**
     * La création d'une validation entraîne l'envoi d'une notification.
     *
     * @param \RapportActivite\Event\Validation\RapportActiviteValidationEvent $event
     */
    public function onValidationAjoutee(RapportActiviteValidationEvent $event)
    {
        /** @var \RapportActivite\Entity\Db\RapportActiviteValidation $rapportValidation */
        $rapportValidation = $event->getTarget();

        Assert::isInstanceOf($rapportValidation, RapportActiviteValidation::class);

        $rapportAvis = $this->rapportActiviteAvisService->findMostRecentRapportAvisForRapport($rapportValidation->getRapport());
        if ($rapportAvis === null) {
            return;
        }

        $this->handleNotification($rapportValidation, $rapportAvis, $event);
    }

    /**
     * La suppression d'une validation entraine la suppression du dernier avis.
     *
     * @param \RapportActivite\Event\Validation\RapportActiviteValidationEvent $event
     */
    public function onValidationSupprimee(RapportActiviteValidationEvent $event)
    {
        /** @var \RapportActivite\Entity\Db\RapportActiviteValidation $rapportValidation */
        $rapportValidation = $event->getTarget();

        Assert::isInstanceOf($rapportValidation, RapportActiviteValidation::class);

        $rapportAvis = $this->rapportActiviteAvisService->findMostRecentRapportAvisForRapport($rapportValidation->getRapport());
        if ($rapportAvis === null) {
            return;
        }

        $this->rapportActiviteAvisService->deleteRapportAvis($rapportAvis);

        $event->setMessages([
            'info' => sprintf(
                "L'avis suivant a été supprimé automatiquement : '%s'",
                $rapportAvis->getAvis()->getAvisType()
            ),
        ]);
    }

    /**
     * Notification.
     *
     * @param \RapportActivite\Entity\Db\RapportActiviteValidation $rapportActiviteValidation
     * @param \RapportActivite\Entity\Db\RapportActiviteAvis $rapportActiviteAvis
     * @param \RapportActivite\Event\Validation\RapportActiviteValidationEvent $event
     */
    private function handleNotification(
        RapportActiviteValidation $rapportActiviteValidation,
        RapportActiviteAvis $rapportActiviteAvis,
        RapportActiviteValidationEvent $event)
    {
        $notif = $this->rapportActiviteValidationService->createRapportActiviteValidationNotification(
            $rapportActiviteValidation,
            $rapportActiviteAvis
        );

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
