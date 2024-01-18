<?php

namespace Admission\Event\Avis;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Entity\Db\Etat;
use Admission\Service\Admission\AdmissionServiceAwareTrait;
use Application\Constants;
use InvalidArgumentException;
use Laminas\EventManager\EventManagerInterface;
use Admission\Entity\Db\AdmissionAvis;
use Admission\Event\Operation\AdmissionOperationAbstractEventListener;
use Admission\Event\AdmissionEvent;
use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\Operation\AdmissionOperationServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Webmozart\Assert\Assert;

/**
 * @property AdmissionAvis $operationRealisee
 */
class AdmissionAvisEventListener extends AdmissionOperationAbstractEventListener
{
    use AdmissionOperationServiceAwareTrait;
    use AdmissionServiceAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->getSharedManager()->attach(
            AdmissionAvisService::class,
            AdmissionAvisService::ADMISSION__AVIS_AJOUTE__EVENT,
            [$this, 'onAvisAjoute']
        );
        $events->getSharedManager()->attach(
            AdmissionAvisService::class,
            AdmissionAvisService::ADMISSION__AVIS_MODIFIE__EVENT,
            [$this, 'onAvisModifie']
        );
    }

    public function onAvisAjoute(AdmissionAvisEvent $event)
    {
        $this->initFromEvent($event);
        $this->handleSuppressionValidationEtudiant();
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET,
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF
        ])) {
            $this->handleNotificationOperationAttendue();
        }
    }

    public function onAvisModifie(AdmissionAvisEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleSuppressionValidationEtudiant();
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET,
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF
        ])) {
            $this->handleNotificationOperationAttendue();
        }
    }

    protected function initFromEvent(AdmissionEvent $event)
    {
        parent::initFromEvent($event);
        Assert::isInstanceOf($this->operationRealisee, AdmissionAvis::class);
    }

    private function handleSuppressionValidationEtudiant(): void
    {
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET
        ])) {
            return;
        }

        $admission = $this->operationRealisee->getAdmission();

        // le nom de l'opération "validation_etudiant_operation_name" est dans la config de l'opération courante
        $operationConfig = $this->admissionOperationRule->getConfigForOperation($this->operationRealisee);

        $ripOperatioName = $operationConfig['extra']['validation_etudiant_operation_name'] ?? null;
        if (!$ripOperatioName) {
            throw new InvalidArgumentException(sprintf(
                "Clé ['extra']['validation_etudiant_operation_name'] introuvable dans la config de l'opération suivante : %s",
                $operationConfig['name']
            ));
        }

        $ripOperationConfig = $this->admissionOperationRule->getConfigForOperationName($ripOperatioName);
        if (!$this->admissionOperationRule->isOperationEnabledForAdmission($ripOperationConfig, $admission)) {
            // opération non activée pour ce dossier d'admission, rien à faire.
            return;
        }

        /** @var AdmissionOperationInterface $ripOperation */
        $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
        $ripOperation = $operations[$ripOperatioName] ?? null;
        if (!$ripOperation->getId()) {
            // opération non réalisée (théoriquement impossible), on abandonne.
            return;
        }

        //Change l'état de en cours de validation à en cours de saisie
        /** @var Etat $enCoursDeValidation */
        $enCoursDeValidation = $this->entityManager->getRepository(Etat::class)->findOneBy(["code" => Etat::CODE_EN_COURS_SAISIE]);
        $admission->setEtat($enCoursDeValidation);
        $this->admissionService->update($admission);

        $messages = [
            'success' => sprintf(
                "L'opération suivante a été annulée car le dossier d'admission a été déclaré incomplet le %s par %s : %s.",
                ($this->operationRealisee->getHistoModification() ?: $this->operationRealisee->getHistoCreation())->format(Constants::DATETIME_FORMAT),
                $this->operationRealisee->getHistoModificateur() ?: $this->operationRealisee->getHistoCreateur(),
                lcfirst($ripOperation),
            ),
        ];

        // historisation (avec déclenchement de l'événement).
        $event = $this->admissionOperationService->deleteOperationAndThrowEvent($ripOperation, $messages);

        $this->event->setMessages($messages);
        $this->event->addMessages($event->getMessages());
    }
}