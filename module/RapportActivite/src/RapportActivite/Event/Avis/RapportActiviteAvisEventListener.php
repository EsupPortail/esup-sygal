<?php

namespace RapportActivite\Event\Avis;

use Application\Constants;
use InvalidArgumentException;
use Laminas\EventManager\EventManagerInterface;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Event\Operation\RapportActiviteOperationAbstractEventListener;
use RapportActivite\Event\RapportActiviteEvent;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Operation\RapportActiviteOperationServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @property \RapportActivite\Entity\Db\RapportActiviteAvis $operationRealisee
 */
class RapportActiviteAvisEventListener extends RapportActiviteOperationAbstractEventListener
{
    use RapportActiviteOperationServiceAwareTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            RapportActiviteAvisService::class,
            RapportActiviteAvisService::RAPPORT_ACTIVITE__AVIS_AJOUTE__EVENT,
            [$this, 'onAvisAjoute']
        );
        $events->getSharedManager()->attach(
            RapportActiviteAvisService::class,
            RapportActiviteAvisService::RAPPORT_ACTIVITE__AVIS_MODIFIE__EVENT,
            [$this, 'onAvisModifie']
        );
    }

    public function onAvisAjoute(RapportActiviteAvisEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleSuppressionValidationDoctorant();
        $this->handleNotificationOperationAttendue();
    }

    public function onAvisModifie(RapportActiviteAvisEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleSuppressionValidationDoctorant();
    }

    protected function initFromEvent(RapportActiviteEvent $event)
    {
        parent::initFromEvent($event);
        Assert::isInstanceOf($this->operationRealisee, RapportActiviteAvis::class);
    }

    private function handleSuppressionValidationDoctorant(): void
    {
        // Si un avis "rapport incomplet" est émis par la direction d'ED, on supprime la validation doctorant.
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET,
            RapportActiviteAvis::AVIS_VALEUR__CODE__AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET,
        ])) {
            return;
        }

        $rapportActivite = $this->operationRealisee->getRapportActivite();

        // le nom de l'opération "validation doctorant" est dans la config de l'opération courante (pratique!).
        $operationConfig = $this->rapportActiviteOperationRule->getConfigForOperation($this->operationRealisee);
        $ripOperatioName = $operationConfig['extra']['validation_doctorant_operation_name'] ?? null;
        if (!$ripOperatioName) {
            throw new InvalidArgumentException(sprintf(
                "Clé ['extra']['validation_doctorant_operation_name'] introuvable dans la config de l'opération suivante : %s",
                $operationConfig['name']
            ));
        }

        $ripOperationConfig = $this->rapportActiviteOperationRule->getConfigForOperationName($ripOperatioName);
        if (!$this->rapportActiviteOperationRule->isOperationEnabledForRapport($ripOperationConfig, $rapportActivite)) {
            // opération non activée pour ce rapport, rien à faire.
            return;
        }

        /** @var \RapportActivite\Entity\RapportActiviteOperationInterface $ripOperation */
        $operations = $this->rapportActiviteOperationRule->getOperationsForRapport($rapportActivite);
        $ripOperation = $operations[$ripOperatioName] ?? null;
        if (!$ripOperation->getId()) {
            // opération non réalisée (théoriquement impossible), on abandonne.
            return;
        }

        $messages = [
            'success' => sprintf(
                "L'opération suivante a été annulée car le rapport a été déclaré incomplet le %s par %s : %s.",
                ($this->operationRealisee->getHistoModification() ?: $this->operationRealisee->getHistoCreation())->format(Constants::DATETIME_FORMAT),
                $this->operationRealisee->getHistoModificateur() ?: $this->operationRealisee->getHistoCreateur(),
                lcfirst($ripOperation),
            ),
        ];

        // historisation (avec déclenchement de l'événement).
        $event = $this->rapportActiviteOperationService->deleteOperationAndThrowEvent($ripOperation, $messages);

        $this->event->setMessages($messages);
        $this->event->addMessages($event->getMessages());
    }
}