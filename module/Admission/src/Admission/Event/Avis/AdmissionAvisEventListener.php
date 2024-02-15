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
        $events->getSharedManager()->attach(
            AdmissionAvisService::class,
            AdmissionAvisService::ADMISSION__AVIS_SUPPRIME__EVENT,
            [$this, 'onAvisSupprime']
        );
    }

    public function onAvisAjoute(AdmissionAvisEvent $event)
    {
        $this->initFromEvent($event);
        $this->handleSuppressionValidationEtudiant();
        $this->handleNotificationAvisAjoute();
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
        $this->handleNotificationAvisModifie();
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET,
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_NEGATIF
        ])) {
            $this->handleNotificationOperationAttendue();
        }
    }

    public function onAvisSupprime(AdmissionAvisEvent $event)
    {
        $this->initFromEvent($event);

        $this->handleNotificationAvisSupprime();
    }

    protected function initFromEvent(AdmissionEvent $event)
    {
        parent::initFromEvent($event);
        Assert::isInstanceOf($this->operationRealisee, AdmissionAvis::class);
    }

    private function handleNotificationAvisAjoute()
    {
        $notif = $this->notificationFactory->createNotificationAvisAjoute($this->operationRealisee);
        $this->triggerNotification($notif);
    }

    private function handleNotificationAvisModifie()
    {
        $notif = $this->notificationFactory->createNotificationAvisModifie($this->operationRealisee);
        $this->triggerNotification($notif);
    }

    private function handleNotificationAvisSupprime()
    {
        $notif = $this->notificationFactory->createNotificationAvisSupprime($this->operationRealisee);
        $this->triggerNotification($notif);
    }

    private function handleSuppressionValidationEtudiant(): void
    {
        if (!in_array($this->operationRealisee->getAvis()->getAvisValeur()->getCode(), [
            AdmissionAvis::AVIS_VALEUR__CODE__AVIS_ADMISSION_VALEUR_INCOMPLET
        ])) {
            return;
        }

        $admission = $this->operationRealisee->getAdmission();

        $operationConfig = $this->admissionOperationRule->getConfigForOperation($this->operationRealisee);
        // le nom de l'opération "validation_etudiant_operation_name" et "validation_gestionnaire_operation_name" est dans la config de l'opération courante
        $ripOperationsname = [
            'validation_convention_formation_doctorale_dir_these_operation_name',
            'validation_convention_formation_doctorale_codir_these_operation_name',
            'validation_convention_formation_doctorale_dir_ur_operation_name',
            'validation_convention_formation_doctorale_dir_ed_operation_name',
            'validation_etudiant_operation_name',
            'validation_gestionnaire_operation_name',
            'avis_direction_these_operation_name',
            'avis_codirection_these_operation_name',
            'avis_direction_ur_operation_name',
            'avis_direction_ed_operation_name',
            'avis_presidence_operation_name'];
        foreach($ripOperationsname as $ripOperationname){
            $ripOperatioName = $operationConfig['extra'][$ripOperationname] ?? null;
            if (!$ripOperatioName) {
                throw new InvalidArgumentException(sprintf(
                    "Clé ['extra'][$ripOperationname] introuvable dans la config de l'opération suivante : %s",
                    $operationConfig['name']
                ));
            }

            $ripOperationConfig = $this->admissionOperationRule->getConfigForOperationName($ripOperatioName);
            if (!$this->admissionOperationRule->isOperationEnabledForAdmission($ripOperationConfig, $admission)) {
                // opération non activée pour ce dossier d'admission, rien à faire.
                continue;
            }

            /** @var AdmissionOperationInterface $ripOperation */
            $operations = $this->admissionOperationRule->getOperationsForAdmission($admission);
            $ripOperation = $operations[$ripOperatioName] ?? null;
            if (!$ripOperation->getId()) {
                // opération non réalisée (théoriquement impossible), on abandonne.
                continue;
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
}