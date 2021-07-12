<?php

namespace Import\Model\Service;

use Application\Constants;
use Import\Model\ImportObserv;
use Import\Model\ImportObservResult;
use Application\Entity\Db\These;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Notification\Notification;
use UnicaenApp\Exception\RuntimeException;
use Laminas\Log\LoggerAwareTrait;

/**
 * @author Unicaen
 */
class ImportObservResultService extends \UnicaenDbImport\Entity\Db\Service\ImportObservResult\ImportObservResultService /** enc. @see \Application\Service\BaseService */
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use VariableServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var NotificationDepotVersionCorrigeeAttenduRule
     */
    protected $notificationDepotVersionCorrigeeAttenduRule;

    /**
     * ImportObservResultService constructor.
     */
    public function __construct()
    {
        $this->setNotificationDepotVersionCorrigeeAttenduRule(new NotificationDepotVersionCorrigeeAttenduRule());
    }

    /**
     * @param NotificationDepotVersionCorrigeeAttenduRule $notificationDepotVersionCorrigeeAttenduRule
     * @return static
     */
    public function setNotificationDepotVersionCorrigeeAttenduRule($notificationDepotVersionCorrigeeAttenduRule)
    {
        $this->notificationDepotVersionCorrigeeAttenduRule = $notificationDepotVersionCorrigeeAttenduRule;

        return $this;
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro.
     *
     * @param \Import\Model\ImportObserv $importObserv Observation concernée
     * @param These|null $these
     * @return void
     */
    public function processImportObservForThese(ImportObserv $importObserv, These $these = null)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                $this->processImportObservResultsForResultatAdmis($importObserv, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                $this->processImportObservResultsForCorrectionFacultative($importObserv, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                $this->processImportObservResultsForCorrectionObligatoire($importObserv, $these);
                break;
            default:
                throw new RuntimeException("Cas non prévu!");
                break;
        }
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses dont le résultat est passé à "admis".
     *
     * @param ImportObserv $importObserv
     * @param These|null $these
     * @throws \Doctrine\ORM\ORMException
     */
    private function processImportObservResultsForResultatAdmis(ImportObserv $importObserv, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses dont le résultat est passé à \"admis\""
        ));

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $these);

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé(s) à traiter.", count($records)));

        if (empty($records)) {
            return;
        }

        // Mise en forme des données pour le template du mail
        $data = $this->prepareDataForResultatAdmis($records);

        // Si aucune donnée n'est retournée, c'est sans doute que les thèses concernées sont historisées, pas de notif.
        if (empty($data)) {
            $this->logger->info("Finalement, rien à faire.");

            return;
        }

        // Notification des doctorants dont le résultat de la thèse est passé à Admis.
        $notifs = $this->notifierService->triggerDoctorantResultatAdmis($data);
        $this->logAboutNotifications($notifs);
        // Notification du BDD concernant l'évolution des résultats de thèses.
        $notif = $this->notifierService->triggerBdDUpdateResultat($data);
        $this->logAboutNotifications([$notif]);

        // Enregistrement de la date de dernière notification
        foreach ($records as $record) {
            $record->setDateNotif($notif->getSendDate());
        }
        try {
            $this->getEntityManager()->flush($records);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResultEtab impossible", null, $e);
        }
    }

    /**
     * @param \Import\Model\ImportObservResult[] $importObservResults
     * @return array
     */
    private function prepareDataForResultatAdmis(array $importObservResults)
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

        $detailsToString = [
            '>1' => "Résultat passé à 'Admis'",
            '>0' => "Résultat passé à 'Non admis'",
            '1>' => "Résultat effacé",
            '0>' => "Résultat effacé",
        ];

        // Mise en forme des données pour le template du mail
        $data = [];
        foreach ($theses as $index => $these) {
            if (!$these->estNonHistorise()) {
                $this->logger->info(sprintf("La thèse '%s' est écartée car elle est historisée.", $these->getSourceCode()));
                continue;
            }
            $detail = trim($details[$index]);
            $detailToString = $detailsToString[$detail] ?? $detail;
            $data[] = [
                'these'  => $these,
                'detail' => $detailToString,
            ];
        }

        return $data;
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "facultative".
     *
     * @param \Import\Model\ImportObserv $importObserv
     * @param These|null $these
     */
    private function processImportObservResultsForCorrectionFacultative(ImportObserv $importObserv, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"facultative\""
        ));

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $these);

        $this->_processImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "obligatoire".
     *
     * @param ImportObserv $importObserv
     * @param These|null $these
     */
    private function processImportObservResultsForCorrectionObligatoire(ImportObserv $importObserv, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"obligatoire\""
        ));

        $records = $this->getRepository()->fetchImportObservResults($importObserv, $these);

        $this->_processImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" a changé.
     *
     * @param ImportObservResult[] $importObservResults
     */
    private function _processImportObservResultsForCorrection(array $importObservResults)
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
            $notif = $this->notifierService->triggerCorrectionAttendue($ior, $these, $message);
            if ($notif === null) {
                $this->logger->info(sprintf("Aucune notif n'est nécessaire pour la thèse '%s'. ", $these->getSourceCode()) . $message);
                continue; // si le service de notif renvoie null, aucune notif n'était nécessaire, on passe au suivant
            }

            $this->logAboutNotifications([$notif]);

            // Enregistrement de la date de dernière notification
            $ior->setDateNotif($notif->getSendDate());

            $recordsToFlush[] = $ior;
        }

        try {
            $this->getEntityManager()->flush($recordsToFlush);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }
    }

    /**
     * @param Notification[] $notifs
     */
    private function logAboutNotifications(array $notifs)
    {
        foreach ($notifs as $notif) {
            $this->logger->info("Notification envoyée : " . $notif);
        }
    }
}
