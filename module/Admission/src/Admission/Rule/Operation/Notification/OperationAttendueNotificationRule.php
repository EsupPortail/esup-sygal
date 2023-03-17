<?php

namespace Admission\Rule\Operation\Notification;

use Admission\Entity\Db\AdmissionOperationInterface;
use Admission\Notification\AdmissionOperationAttenduNotification;
use Admission\Rule\Email\ExtractionEmailRuleAwareTrait;
use Admission\Rule\Operation\AdmissionOperationRuleAwareTrait;
use Application\Rule\RuleInterface;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use These\Service\Acteur\ActeurServiceAwareTrait;
use UnicaenApp\Traits\MessageAwareTrait;

/**
 * Règles métiers concernant la notification en cas d'opération attendue sur un dossier d'admission.
 */
class OperationAttendueNotificationRule implements RuleInterface
{
    use AdmissionOperationRuleAwareTrait;
    use ActeurServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use MessageAwareTrait;
    use ExtractionEmailRuleAwareTrait;

    private AdmissionOperationInterface $operationRealisee;
    private AdmissionOperationInterface $operationAttendue;
    private bool $notificationRequired;
    private array $to;
    private string $subject;
    private array $anomalies = [];

    public function setOperationRealisee(AdmissionOperationInterface $operationRealisee): self
    {
        $this->operationRealisee = $operationRealisee;
        return $this;
    }

    public function execute(): self
    {
        // On notifie à propos de l'opération suivante attendue, quelle qu'elle soit.

        $operationAttendue = $this->admissionOperationRule->findFollowingOperation($this->operationRealisee);
        if ($operationAttendue === null) {
            // Aucune opération après : bye !
            $this->notificationRequired = false;
            return $this;
        }

        $operationAttendueConfig = $this->admissionOperationRule->getConfigForOperation($operationAttendue);
        $operationAttendueIsAuto = $operationAttendueConfig['readonly'] ?? false;
        if ($operationAttendueIsAuto) {
            // L'opération attendue ensuite est "automatique" (sans intervention humaine), personne à notifier : bye !
            $this->notificationRequired = false;
            return $this;
        }

        $this->operationAttendue = $operationAttendue;

        $this->handleOperationAttendue();

        return $this;
    }

    private function handleOperationAttendue()
    {
        $admission = $this->operationRealisee->getAdmission();

        $followingOperationConfig = $this->admissionOperationRule->getConfigForOperation($this->operationAttendue);
        $to = $this->extractionMailRule->extractEmailsFromAdmissionRoles($admission, $followingOperationConfig);
        $this->anomalies = $this->extractionMailRule->getAnomalies();

        $this->notificationRequired = true;
        $this->to = $to;

        $this->subject = sprintf("%s de %s",
            $this->operationAttendue->getAdmission(),
            $admission->getIndividu()->getNomComplet(),
        );
    }

    public function configureNotification(AdmissionOperationAttenduNotification $notif)
    {
        $notif->setTo($this->to);
        $notif->setSubject($this->subject);

        $notif->setOperationAttendue($this->operationAttendue);
        $notif->setAnomalies($this->anomalies);
    }

    public function isNotificationRequired(): bool
    {
        return $this->notificationRequired;
    }
}