<?php

namespace Import\Service\ImportObservEtabResult;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservEtabResult;
use Application\Entity\Db\Repository\ImportObservEtabResultRepository;
use Application\Entity\Db\These;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\BaseService;
use Application\Service\Notification\NotifierServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use Notification\Notification;
use UnicaenApp\Exception\RuntimeException;
use Zend\Log\LoggerAwareTrait;

/**
 * @author Unicaen
 */
class ImportObservEtabResultService extends BaseService
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use VariableServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var ImportObservEtabResultRepository
     */
    private $repository;

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
     * @param ImportObservEtabResultRepository $repository
     * @return static
     */
    public function setRepository(ImportObservEtabResultRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return ImportObservEtabResultRepository
     */
    public function getRepository()
    {
        return $this->repository;
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
     * @param ImportObserv         $importObserv  Observation voulue
     * @param Etablissement|string $etablissement Etablissement concerné
     * @param These|null           $these
     * @return void
     */
    public function handleImportObservEtabResults(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                $this->handleImportObservResultsForResultatAdmis($importObserv, $etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_FACULTATIVE:
                $this->handleImportObservResultsForCorrectionFacultative($importObserv, $etablissement, $these);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_OBLIGATOIRE:
                $this->handleImportObservResultsForCorrectionObligatoire($importObserv, $etablissement, $these);
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
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     */
    private function handleImportObservResultsForResultatAdmis(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses dont le résultat est passé à \"admis\"",
            $etablissement));

        $records = $this->repository->fetchImportObservEtabResults($importObserv, $etablissement, $these);

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
            throw new RuntimeException("Enregistrement des ImportObservEtabResult impossible", null, $e);
        }
    }

    /**
     * @param ImportObservEtabResult[] $records
     * @return array
     */
    private function prepareDataForResultatAdmis($records)
    {
        // Mise en forme des résultats d'observation
        $sourceCodes = [];
        $details = [];
        foreach ($records as $record) {
            if ($record->isTooOld()) {
                $this->logger->info(sprintf("Le ImportObservEtabResult %d de la thèse '%s' est écarté car TOO_OLD = 1.",
                    $record->getId(),
                    $record->getSourceCode()
                ));
                continue;
            }
            $sourceCodes[] = $record->getSourceCode();
            $details[] = $record->getResultatToString();
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
            $data[] = [
                'these'  => $these,
                'detail' => $details[$index],
            ];
        }

        return $data;
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "facultative".
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     */
    private function handleImportObservResultsForCorrectionFacultative(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"facultative\"",
            $etablissement
        ));

        $records = $this->repository->fetchImportObservEtabResults($importObserv, $etablissement, $these);

        $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "obligatoire".
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement
     * @param These|null           $these
     */
    private function handleImportObservResultsForCorrectionObligatoire(ImportObserv $importObserv, $etablissement, These $these = null)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"obligatoire\"",
            $etablissement
        ));

        $records = $this->repository->fetchImportObservEtabResults($importObserv, $etablissement, $these);

        $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" a changé.
     *
     * @param ImportObservEtabResult[] $records
     */
    private function _handleImportObservResultsForCorrection(array $records)
    {
        if (!count($records)) {
            $this->logger->info("Aucun résultat d'import à traiter.");

            return;
        }

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé(s).", count($records)));

        $recordsToFlush = [];

        foreach ($records as $record) {
            /** @var These $these */
            $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $record->getSourceCode()]);
            if (!$these->estNonHistorise()) {
                $this->logger->info(sprintf("La thèse '%s' est écartée car elle est historisée.", $these->getSourceCode()));
                continue;
            }
            if ($record->isTooOld()) {
                $this->logger->info(sprintf("Le ImportObservEtabResult %d de la thèse '%s' est écarté car TOO_OLD = 1.",
                    $record->getId(),
                    $record->getSourceCode()
                ));
                continue;
            }

            $these->setCorrectionAutorisee($record->getImportObservEtab()->getImportObserv()->getToValue()); // anticipation nécessaire !

            // notification
            $notif = $this->notifierService->triggerCorrectionAttendue($record, $these);
            if ($notif === null) {
                $this->logger->info(sprintf("D'après les règles métiers, aucune notif n'est nécessaire pour la thèse '%s'.", $these->getSourceCode()));
                continue; // si le service de notif renvoie null, aucune notif n'était nécessaire, on passe au suivant
            }

            $this->logAboutNotifications([$notif]);

            // Enregistrement de la date de dernière notification
            $record->setDateNotif($notif->getSendDate());

            $recordsToFlush[] = $record;
        }

        try {
            $this->getEntityManager()->flush($recordsToFlush);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservEtabResult impossible", null, $e);
        }
    }

    /**
     * @param Notification[] $notifs
     */
    private function logAboutNotifications(array $notifs)
    {
        foreach ($notifs as $notif) {
            $this->logger->info(
                sprintf('Notification "%s" envoyée : %s.', $notif->getSubject(), implode(", ", $notif->getTo()), $notifs)
            );
        }
    }
}
