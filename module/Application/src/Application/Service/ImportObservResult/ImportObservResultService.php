<?php

namespace Application\Service\ImportObservResult;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Application\Entity\Db\These;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\BaseService;
use Application\Service\Notification\NotificationServiceAwareInterface;
use Application\Service\Notification\NotificationServiceAwareTrait;
use Application\Service\These\TheseServiceAwareInterface;
use Application\Service\These\TheseServiceAwareTrait;
use Zend\View\Model\ViewModel;

/**
 * @author Unicaen
 */
class ImportObservResultService extends BaseService
{
    use TheseServiceAwareTrait;
    use NotificationServiceAwareTrait;

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

        $data = [];
        foreach ($theses as $index => $these) {
            $data[] = [
                'these'  => $these,
                'detail' => $details[$index],
            ];
        }

        $this->notificationService->notifierBdDUpdateResultat($data);
        $this->notificationService->notifierDoctorantResultatAdmis($data);

        // Enregistrement de la date de notification sur chaque résultat d'observation
        foreach ($records as $record) {
            $record->setDateNotif(new \DateTime());
        }
        $this->getEntityManager()->flush($records);

        return $this;
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
     * @param array $records
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

            $rule = $this->notificationDepotVersionCorrigeeAttenduRule;
            $rule
                ->setThese($these)
                ->setDateDerniereNotif($record->getDateNotif())
                ->execute();
            $estPremiereNotif = $rule->estPremiereNotif();
            $dateProchaineNotif = $rule->getDateProchaineNotif();
            if ($dateProchaineNotif === null) {
                continue;
            }

            $dateProchaineNotif->setTime(0, 0, 0);
            $now = (new \DateTime())->setTime(0, 0, 0);
            if ($now != $dateProchaineNotif) {
                continue;
            }

            // notification
            $viewModel = new ViewModel([
                'subject' => "Dépôt de thèse, corrections " . lcfirst($these->getCorrectionAutoriseeToString()) . "s attendues",
                'estPremiereNotif' => $estPremiereNotif,
            ]);
            $directeursTheseEnCopie = false;
            if ($these->getCorrectionAutoriseeEstMajeure() && !$estPremiereNotif) {
                $directeursTheseEnCopie = true;
            }
            $this->notificationService->notifierCorrectionAttendue($viewModel, $these, $directeursTheseEnCopie);

            // Enregistrement de la date de notification
            $record->setDateNotif(new \DateTime());
            $recordsToFlush[] = $record;
        }

        $this->getEntityManager()->flush($recordsToFlush);

        return $this;
    }
}
