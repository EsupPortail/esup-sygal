<?php

namespace Application\Service\ImportObservResult;

use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\Variable;
use Application\Notification\ResultatTheseAdmisNotification;
use Application\Notification\ResultatTheseModifieNotification;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\BaseService;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Doctrine\ORM\OptimisticLockException;
use UnicaenApp\Exception\RuntimeException;

/**
 * @author Unicaen
 */
class ImportObservResultService extends BaseService
{
    use TheseServiceAwareTrait;
    use NotificationServiceAwareTrait;
    use VariableServiceAwareTrait;

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
     *
     * @return static
     */
    public function handleImportObservResultsForResultatAdmis()
    {
        $records = $this->repository->fetchImportObservResultsForResultatAdmis();
        if (! count($records)) {
            return $this;
        }

        // Mise en forme des données pour le template du mail
        $data = $this->prepareDataForResultatAdmis($records);

        // Notification :
        // - du BDD concernant l'évolution des résultats de thèses.
        // - des doctorants dont le résultat de la thèse est passé à Admis.
        $this->notificationService->triggerBdDUpdateResultat($data);
        $this->notificationService->triggerDoctorantResultatAdmis($data);

        // Enregistrement de la date de notification sur chaque résultat d'observation
        foreach ($records as $record) {
            $record->setDateNotif(new \DateTime());
        }
        try {
            $this->getEntityManager()->flush($records);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Enregistrement des ImportObservResult impossible", null, $e);
        }

        return $this;
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
        $qb->where($qb->expr()->in('t.sourceCode', $sourceCodes));
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
     *
     * @return static
     */
    public function handleImportObservResultsForCorrectionMineure()
    {
        $records = $this->repository->fetchImportObservResultsForCorrectionMineure();

        return $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" est passé à "majeure".
     *
     * @return static
     */
    public function handleImportObservResultsForCorrectionMajeure()
    {
        $records = $this->repository->fetchImportObservResultsForCorrectionMajeure();

        return $this->_handleImportObservResultsForCorrection($records);
    }

    /**
     * Traitement des résultats d'observation des changements lors de la synchro :
     * notifications au sujet des thèses pour lesquelles le témoin "correction autorisée" a changé.
     *
     * @param ImportObservResult[] $records
     * @return static
     */
    private function _handleImportObservResultsForCorrection(array $records)
    {
        if (! count($records)) {
            return $this;
        }

        $recordsToFlush = [];

        foreach ($records as $record) {
            /** @var These $these */
            $these = $this->theseService->getRepository()->findOneBy(['sourceCode' => $record->getSourceCode()]);
            $these->setCorrectionAutorisee($record->getImportObserv()->getToValue()); // anticipation nécessaire !

            // notification
            $result = $this->notificationService->triggerCorrectionAttendue($record, $these);
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

        return $this;
    }
}
