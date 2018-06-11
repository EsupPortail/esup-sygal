<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\These;
use Application\ORM\Query\Functions\Year;
use Application\QueryBuilder\TheseQueryBuilder;

/**
 * @method TheseQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class TheseRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = TheseQueryBuilder::class;

    /**
     * @return These[]
     */
    public function fetchThesesWithDateButoirDepotVersionCorrigeeDepassee()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->andWhereEtatIs(These::ETAT_EN_COURS)
            ->andWhere('t.dateSoutenance is not null')
            ->andWhere($qb->expr()->in('t.correctionAutorisee', [These::CORRECTION_MAJEURE, These::CORRECTION_MINEURE]));

        $theses = array_filter($qb->getQuery()->getResult(), function (These $these) {
            return $these->getDateButoirDepotVersionCorrigeeDepassee();
        });

        return $theses;
    }

    /**
     * @return array
     * @see Year
     */
    public function fetchDistinctAnneesPremiereInscription()
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("year(t.datePremiereInscription)")
            ->orderBy("year(t.datePremiereInscription)");

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }
}