<?php

namespace Import\Model\Service;

use Application\Constants;
use Application\Service\Variable\VariableServiceAwareTrait;
use Depot\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Doctrine\ORM\Exception\ORMException;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Import\Model\Repository\ImportObservResultRepository;
use Laminas\Log\LoggerAwareTrait;
use Notification\Notification;
use Notification\Service\NotifierServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Notification\TheseNotificationFactoryAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenApp\Exception\RuntimeException;

/**
 * @method ImportObservResultRepository getRepository()
 */
class ImportObservResultService extends \UnicaenDbImport\Entity\Db\Service\ImportObservResult\ImportObservResultService
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use TheseNotificationFactoryAwareTrait;
    use VariableServiceAwareTrait;
    use LoggerAwareTrait;

    protected NotificationDepotVersionCorrigeeAttenduRule $notificationDepotVersionCorrigeeAttenduRule;

    public function __construct()
    {
        $this->setNotificationDepotVersionCorrigeeAttenduRule(new NotificationDepotVersionCorrigeeAttenduRule());
    }

    public function setNotificationDepotVersionCorrigeeAttenduRule(NotificationDepotVersionCorrigeeAttenduRule $notificationDepotVersionCorrigeeAttenduRule): static
    {
        $this->notificationDepotVersionCorrigeeAttenduRule = $notificationDepotVersionCorrigeeAttenduRule;

        return $this;
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro.
     */
    public function processImportObserv(ImportObserv $importObserv, array $criteria = []): void
    {
        $this->logger->info("###################################################################");

        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                $this->processImportObservResultsForResultatAdmis($importObserv, $criteria);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                $this->processImportObservResultsForCorrectionFacultative($importObserv, $criteria);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                $this->processImportObservResultsForCorrectionObligatoire($importObserv, $criteria);
                break;
            default:
                throw new RuntimeException("Cas non prévu!");
        }
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses dont le résultat est passé à "admis".
     */
    private function processImportObservResultsForResultatAdmis(ImportObserv $importObserv, array $criteria = []): void
    {
        $this->logger->info(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses dont le résultat est passé à \"admis\""
        );

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $criteria);

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé(s) à traiter.", count($records)));

        if (empty($records)) {
            return;
        }

        foreach ($records as $ior) {
            $this->logger->info(sprintf("- %s", $ior));
        }

        // Mise en forme des données pour le template du mail
        $data = $this->prepareDataForResultatAdmis($records);

        // Si aucune donnée n'est retournée, c'est sans doute que les thèses concernées sont historisées, pas de notif.
        if (empty($data)) {
            $this->logger->info("Finalement, rien à faire.");

            return;
        }

        //
        // Notification des doctorants dont le résultat de la thèse est passé à 'Admis'.
        //
        try {
            $notifs = $this->theseNotificationFactory->createNotificationsChangementResultatThesesAdmisDoctorant($data);
            foreach ($notifs as $notif) {
                $this->notifierService->trigger($notif);
            }
            $this->logAboutNotifications($notifs);
        } catch (\Notification\Exception\RuntimeException $e) {
            // pb de destinataire, todo : cas à gérer !
            error_log("Impossible d'envoyer la notification aux doctorants dont le résultat de la thèse est passé à Admis !");
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
        }

        //
        // Notification des gestionnaires concernant l'évolution des résultats de thèses.
        //
        try {
            $notif = $this->theseNotificationFactory->createNotificationChangementResultatThesesGestionnaires($data);
            $result = $this->notifierService->trigger($notif);
            $this->logAboutNotifications([$notif]);

            // Enregistrement de la date de dernière notification
            foreach ($records as $record) {
                $record->setDateNotif($result->getSendDate());
            }
            try {
                $this->getEntityManager()->flush($records);
            } catch (ORMException $e) {
                throw new RuntimeException("Enregistrement des ImportObservResultEtab impossible", null, $e);
            }
        } catch (\Notification\Exception\RuntimeException $e) {
            // pb de destinataire, todo : cas à gérer !
            error_log("Impossible d'envoyer les notifications aux gestionnaires concernant l'évolution des résultats de thèses !");
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    /**
     * @param \Import\Model\ImportObservResult[] $importObservResults
     */
    private function prepareDataForResultatAdmis(array $importObservResults): array
    {
        // Mise en forme des résultats d'observation
        $sourceCodes = [];
        $details = [];
        foreach ($importObservResults as $ior) {
            if ($ior->isDateLimiteNotifDepassee()) {
                $this->logger->info(sprintf(
                    "Le ImportObservResult %d de la thèse '%s' est écarté car la date limite de notif est dépassée (%s).",
                    $ior->getId(),
                    $ior->getSourceCode(),
                    $ior->getDateLimiteNotif()->format(Constants::DATETIME_FORMAT)
                ));
                continue;
            }
            $sourceCodes[] = $ior->getSourceCode();
            $details[] = $ior->getResultatToString();
        }

        if (empty($sourceCodes)) {
            return [];
        }

        // Fetch thèses concernées
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb->where($qb->expr()->in('t.sourceCode', $sourceCodes));
        $theses = $qb->getQuery()->getResult();
        /** @var These[] $theses */

        // Mise en forme des données pour le template du mail
        $data = [];
        foreach ($theses as $index => $these) {
            if (!$these->estNonHistorise()) {
                $this->logger->info(sprintf("La thèse '%s' est écartée car elle est historisée.", $these->getSourceCode()));
                continue;
            }
            $detail = trim($details[$index]);
            $data[] = [
                'these' => $these,
                'detail' => $detail,
            ];
        }

        return $data;
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "facultative".
     */
    private function processImportObservResultsForCorrectionFacultative(ImportObserv $importObserv, array $criteria = []): void
    {
        $this->logger->info(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"facultative\""
        );

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $criteria);

        $this->_processImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "obligatoire".
     */
    private function processImportObservResultsForCorrectionObligatoire(ImportObserv $importObserv, array $criteria = []): void
    {
        $this->logger->info(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"obligatoire\""
        );

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $criteria);

        $this->_processImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" a changé.
     *
     * @param ImportObservResult[] $importObservResults
     */
    private function _processImportObservResultsForCorrection(array $importObservResults): void
    {
        if (!count($importObservResults)) {
            $this->logger->info("Aucun résultat d'import à traiter.");

            return;
        }

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé(s).", count($importObservResults)));

        $recordsToFlush = [];

        foreach ($importObservResults as $ior) {
            $this->logger->info(sprintf("- %s", $ior));

            /** @var These $these */
            $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $ior->getSourceCode()]);
            if (!$these->estNonHistorise()) {
                $this->logger->info(sprintf("La thèse '%s' est écartée car elle est historisée.", $these->getSourceCode()));
                continue;
            }
            if ($ior->isDateLimiteNotifDepassee()) {
                $this->logger->info(sprintf(
                    "Le ImportObservResult %d de la thèse '%s' est écarté car la date limite de notif est dépassée (%s).",
                    $ior->getId(),
                    $ior->getSourceCode(),
                    $ior->getDateLimiteNotif()->format(Constants::DATETIME_FORMAT)
                ));
                continue;
            }

            $these->setCorrectionAutorisee($ior->getImportObserv()->getToValue()); // anticipation nécessaire !

            // notification
            try {
                $notif = $this->theseNotificationFactory->createNotificationCorrectionAttendue($ior, $these, $message);
                if ($notif === null) {
                    $this->logger->info(sprintf("Aucune notif n'est nécessaire pour la thèse '%s'. ", $these->getSourceCode()) . $message);
                    continue; // si le service de notif renvoie null, aucune notif n'était nécessaire, on passe au suivant
                }
                $result = $this->notifierService->trigger($notif);
                $this->logAboutNotifications([$notif]);// Enregistrement de la date de dernière notification

                $ior->setDateNotif($result->getSendDate());
                $recordsToFlush[] = $ior;
            } catch (\Notification\Exception\RuntimeException $e) {
                // aucun destinataire, todo : cas à gérer !
            }
        }

        try {
            $this->getEntityManager()->flush($recordsToFlush);
        } catch (ORMException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }
    }

    /**
     * @param Notification[] $notifs
     */
    private function logAboutNotifications(array $notifs): void
    {
        foreach ($notifs as $notif) {
            $this->logger->info("Notification envoyée : " . $notif);
        }
    }
}
