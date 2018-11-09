<?php

namespace Import\Service\ImportObservResult;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Repository\ImportObservResultRepository;
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
class ImportObservResultService extends BaseService
{
    use TheseServiceAwareTrait;
    use NotifierServiceAwareTrait;
    use VariableServiceAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var ImportObservResultRepository
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
     * @param ImportObservResultRepository $repository
     * @return static
     */
    public function setRepository(ImportObservResultRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return DefaultEntityRepository
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
     * @return void
     */
    public function handleImportObservResults(ImportObserv $importObserv, $etablissement)
    {
        switch ($importObserv->getCode()) {
            case ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS:
                $this->handleImportObservResultsForResultatAdmis($importObserv, $etablissement);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_MINEURE:
                $this->handleImportObservResultsForCorrectionMineure($importObserv, $etablissement);
                break;
            case ImportObserv::CODE_CORRECTION_PASSE_A_MAJEURE:
                $this->handleImportObservResultsForCorrectionMajeure($importObserv, $etablissement);
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
     */
    private function handleImportObservResultsForResultatAdmis(ImportObserv $importObserv, $etablissement)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses dont le résultat est passé à \"admis\"",
            $etablissement));

        $records = $this->repository->fetchImportObservResults($importObserv, $etablissement);

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé(s) à traiter.", count($records)));

        if (empty($records)) {
            return;
        }

        // Mise en forme des données pour le template du mail
        $data = $this->prepareDataForResultatAdmis($records);

        // Si aucune donnée n'est retournée, c'est sans doute que les thèses concernées sont historisées, pas de notif.
        if (empty($data)) {
            $this->logger->info("Finalement, rien à faire (source codes inexistants ou thèses concernées historisées.");

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
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }
    }

    /**
     * @param ImportObservResult[] $records
     * @return array
     */
    private function prepareDataForResultatAdmis($records)
    {
        // Mise en forme des résultats d'observation
        $sourceCodes = [];
        $details = [];
        foreach ($records as $record) {
            $sourceCodes[] = $record->getSourceCode();
            $details[] = $record->getResultatToString();
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
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "mineure".
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement
     */
    private function handleImportObservResultsForCorrectionMineure(ImportObserv $importObserv, $etablissement)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"mineure\"",
            $etablissement
        ));

        $records = $this->repository->fetchImportObservResults($importObserv, $etablissement);

        $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "majeure".
     *
     * @param ImportObserv         $importObserv
     * @param Etablissement|string $etablissement
     */
    private function handleImportObservResultsForCorrectionMajeure(ImportObserv $importObserv, $etablissement)
    {
        $this->logger->info(sprintf(
            "# Traitement des résultats d'import de l'établissement '%s' : " .
            "notifications au sujet des thèses pour lesquelles le témoin \"correction autorisée\" est passé à \"majeure\"",
            $etablissement
        ));

        $records = $this->repository->fetchImportObservResults($importObserv, $etablissement);

        $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" a changé.
     *
     * @param ImportObservResult[] $records
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

            $these->setCorrectionAutorisee($record->getImportObserv()->getToValue()); // anticipation nécessaire !

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
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
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
