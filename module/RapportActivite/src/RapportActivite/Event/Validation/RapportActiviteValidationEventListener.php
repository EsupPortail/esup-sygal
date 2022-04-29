<?php

namespace RapportActivite\Event\Validation;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use RapportActivite\Controller\Validation\RapportActiviteValidationController;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Service\Avis\RapportActiviteAvisServiceAwareTrait;
use Webmozart\Assert\Assert;

class RapportActiviteValidationEventListener implements ListenerAggregateInterface
{
    use RapportActiviteAvisServiceAwareTrait;

    use ListenerAggregateTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteValidationController::class,
            RapportActiviteValidationController::RAPPORT_ACTIVITE__VALIDATION_SUPPRIMEE__EVENT,
            [$this, 'onValidationSupprimee']
        );
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
}
