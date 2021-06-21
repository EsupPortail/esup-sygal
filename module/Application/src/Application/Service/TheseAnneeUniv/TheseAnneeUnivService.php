<?php

namespace Application\Service\TheseAnneeUniv;

use Application\Entity\AnneeUniv;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\TheseAnneeUniv;
use Application\Service\BaseService;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use stdClass;

class TheseAnneeUnivService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(TheseAnneeUniv::class);
    }

    /**
     * @param Etablissement|null $etablissement
     * @param bool $cacheable
     * @return int[]
     */
    public function fetchDistinctAnneesUniv1ereInscription(Etablissement $etablissement = null, $cacheable = false): array
    {
        $qb = $this->getRepository()->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("t.anneeUniv")
            ->orderBy("t.anneeUniv");

        if ($etablissement !== null) {
            $qb
                ->join('t.these', 'th')
                ->join('th.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $qb->setCacheable($cacheable);

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }

    /**
     * @return AnneeUniv
     *
     */
    public function anneeUnivCourante(): AnneeUniv
    {
        return AnneeUniv::courante();
    }
}