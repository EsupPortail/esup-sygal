<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\ImportObserv;
use Application\Entity\Db\ImportObservResult;
use Doctrine\ORM\Query\Expr\Join;

class ImportObservResultRepository extends DefaultEntityRepository
{
    /**
     * Recherche des résultats d'observation des thèses dont le résultat est passé à "admis".
     *
     * @return ImportObservResult[]
     */
    public function fetchImportObservResultsForResultatAdmis()
    {
        $qb = $this->createQueryBuilder('ior')
            ->join('ior.importObserv', 'io', Join::WITH, 'io.code = :code')
            ->andWhere('io.enabled = 1')
            ->andWhere('ior.dateNotif is null') // aucune notification ne doit avoir été faite
            ->setParameter('code', ImportObserv::CODE_RESULTAT_PASSE_A_ADMIS);

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "mineure".
     *
     * @return ImportObservResult[]
     */
    public function fetchImportObservResultsForCorrectionMineure()
    {
        $qb = $this->createQueryBuilder('ior')
            ->join('ior.importObserv', 'io', Join::WITH, 'io.code = :code')
            ->andWhere('io.enabled = 1')
            ->andWhere('ior.dateNotif is null') // aucune notification ne doit avoir été faite
            ->setParameter('code', ImportObserv::CODE_CORRECTION_PASSE_A_MINEURE);

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }

    /**
     * Recherche des résultats d'observation des thèses dont le flag "correction_autorisee" est passé à "majeure".
     *
     * @return ImportObservResult[]
     */
    public function fetchImportObservResultsForCorrectionMajeure()
    {
        $qb = $this->createQueryBuilder('ior')
            ->join('ior.importObserv', 'io', Join::WITH, 'io.code = :code')
            ->andWhere('io.enabled = 1')
            ->andWhere('ior.dateNotif is null OR ior.dateNotif is not null') // une notif peut avoir été faite ou non
            ->setParameter('code', ImportObserv::CODE_CORRECTION_PASSE_A_MAJEURE);

        /** @var ImportObservResult[] $records */
        $records = $qb->getQuery()->getResult();

        return $records;
    }
}