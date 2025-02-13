<?php

namespace Depot\Process\Validation;

use Application\Service\UserContextServiceAwareTrait;
use Depot\Service\Notification\DepotNotificationFactoryAwareTrait;
use Depot\Service\Validation\DepotValidationServiceAwareTrait;
use Exception;
use Notification\Service\NotifierServiceAwareTrait;
use RuntimeException;
use These\Entity\Db\These;
use These\Service\These\TheseServiceAwareTrait;
use Validation\Entity\Db\ValidationThese;

class DepotValidationProcess
{
    use DepotValidationServiceAwareTrait;
    use TheseServiceAwareTrait;
    use DepotValidationServiceAwareTrait;
    use UserContextServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use DepotNotificationFactoryAwareTrait;

    private array $notificationLogs;

    public function validateCorrectionThese(These $these): ValidationThese
    {
        $em = $this->depotValidationService->getEntityManager();
        $em->beginTransaction();

        $validation = $this->depotValidationService->validateCorrectionThese($these);

        // on ne va pas plus loin s'il reste des validations attendues
        $validationsAttendues = $this->depotValidationService->getValidationsAttenduesPourCorrectionThese($these);
        if (count($validationsAttendues) > 0) {
            return $validation;
        }

        // Met à jour le témoin correction effectuée pour une thèse provenant de SyGAL
        if (!$these->getSource()->getImportable()) {
            $these->setCorrectionEffectuee("O");
            $this->theseService->update($these);
        }

        $notificationFailed = false;
        $this->notificationLogs = [];

        // notification de la MDD
        try {
            $notification = $this->depotNotificationFactory->createNotificationValidationCorrectionThese($these);
            $notificationResult = $this->notifierService->trigger($notification);
            $this->notificationLogs = array_merge_recursive($this->notificationLogs, array_filter([
                'success' => $notificationResult->getSuccessMessages(),
                'danger' => $notificationResult->getErrorMessages(),
            ]));
        } catch (\Notification\Exception\RuntimeException $e) {
            $notificationFailed = true;
            $this->notificationLogs = array_merge_recursive($this->notificationLogs, [
                'danger' => $e->getMessage(),
            ]);
        }

        // notification du doctorant
        try {
            $notification = $this->depotNotificationFactory->createNotificationValidationCorrectionTheseEtudiant($these);
            $notificationResult = $this->notifierService->trigger($notification);
            $this->notificationLogs = array_merge_recursive($this->notificationLogs, array_filter([
                'success' => $notificationResult->getSuccessMessages(),
                'danger' => $notificationResult->getErrorMessages(),
            ]));
        } catch (\Notification\Exception\RuntimeException $e) {
            $notificationFailed = true;
            $this->notificationLogs = array_merge_recursive($this->notificationLogs, [
                'danger' => $e->getMessage(),
            ]);
        }

        if ($notificationFailed) {
            throw new RuntimeException("Problème rencontré lors de l'envoi des notifications.");
        }

        try {
            $em->commit();
        } catch (Exception $e) {
            $em->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd, rollback", null, $e);
        }

        return $validation;
    }

    public function unvalidateCorrectionThese(These $these): ValidationThese
    {
        $em = $this->depotValidationService->getEntityManager();
        $em->beginTransaction();

        $validation = $this->depotValidationService->unvalidateCorrectionThese($these);

        // pas de notification par mail

        try {
            $em->commit();
        } catch (Exception $e) {
            $em->rollback();
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd, rollback", null, $e);
        }

        return $validation;
    }

    public function getNotificationLogs(): array
    {
        return $this->notificationLogs;
    }
}