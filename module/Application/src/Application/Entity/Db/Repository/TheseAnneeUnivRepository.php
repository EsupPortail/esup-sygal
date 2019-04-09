<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Doctrine\ORM\Query\Expr\Join;

class TheseAnneeUnivRepository extends DefaultEntityRepository
{
    /**
     * @param Etablissement|null $etablissement
     * @return int[]
     */
    public function fetchDistinctAnneesUniv1ereInscription(Etablissement $etablissement = null)
    {
        $qb = $this->createQueryBuilder('t');
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

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }
}
