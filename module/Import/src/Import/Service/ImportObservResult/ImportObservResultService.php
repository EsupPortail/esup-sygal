<?php

namespace Import\Service\ImportObservResult;

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
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses dont le résultat est passé à "admis".
     */
    public function handleImportObservResultsForResultatAdmis()
    {
        $records = $this->repository->fetchImportObservResultsForResultatAdmis();
        if (! count($records)) {
            $this->logger->info("Aucun résultat d'import à traiter.");
            return;
        }

        $this->logger->info(sprintf("%d résultat(s) d'import trouvé.", count($records)));

        // Mise en forme des données pour le template du mail
        $data = $this->prepareDataForResultatAdmis($records);

        // Si aucune donnée n'est retournée, c'est sans doute que les thèses concernées sont historisées, pas de notif.
        if (empty($data)) {
            $this->logger->info("Finalement, rien à faire (source codes inexistants ou thèses concernées historisées.");
            return;
        }

        // Notification :
        // - du BDD concernant l'évolution des résultats de thèses.
        // - des doctorants dont le résultat de la thèse est passé à Admis.
        $notif = $this->notifierService->triggerBdDUpdateResultat($data);
        $this->logger->info(sprintf("Notification envoyée à %s.",
            implode(", ", $notif->getTo())
        ));
        $notifs = $this->notifierService->triggerDoctorantResultatAdmis($data);
        $this->logger->info(sprintf("Notification envoyée à %s.",
            implode(", ", array_map(function (Notification $notif) { return implode(", ", $notif->getTo()); }, $notifs))
        ));

        // Enregistrement de la date de notification sur chaque résultat d'observation
        foreach ($records as $record) {
            $record->setDateNotif(new \DateTime());
        }
        try {
            $this->getEntityManager()->flush($records);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }

        $this->logger->info("Enregistrement de la date de notification sur chaque résultat d'observation effectué.");
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
            $details[]     = $record->getResultatToString();
        }

        // Fetch thèses concernées
        $qb = $this->theseService->getRepository()->createQueryBuilder('t');
        $qb
            ->where($qb->expr()->in('t.sourceCode', $sourceCodes))
            ->andWhere('1 = pasHistorise(t)');
        /** @var These[] $theses */
        $theses = $qb->getQuery()->getResult();

        // Mise en forme des données pour le template du mail
        $data = [];
        foreach ($theses as $index => $these) {
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
     */
    public function handleImportObservResultsForCorrectionMineure()
    {
        $records = $this->repository->fetchImportObservResultsForCorrectionMineure();

        $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "majeure".
     */
    public function handleImportObservResultsForCorrectionMajeure()
    {
        $records = $this->repository->fetchImportObservResultsForCorrectionMajeure();

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
        if (! count($records)) {
            return;
        }

        $recordsToFlush = [];

        foreach ($records as $record) {
            /** @var These $these */
            $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $record->getSourceCode()]);
            $these->setCorrectionAutorisee($record->getImportObserv()->getToValue()); // anticipation nécessaire !

            // notification
            $result = $this->notifierService->triggerCorrectionAttendue($record, $these);
            if ($result === null) {
                continue; // si le service de notif renvoie null, aucune notif n'était nécessaire, on passe au suivant
            }

            // Enregistrement de la date de notification
            $record->setDateNotif(new \DateTime());

            $recordsToFlush[] = $record;
        }

        try {
            $this->getEntityManager()->flush($recordsToFlush);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }
    }
}
