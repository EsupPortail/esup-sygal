<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\ORM\Query\Functions\Year;
use Application\QueryBuilder\TheseQueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

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
            ->andWhereCorrectionAutorisee()
            ->andWhere('t.dateSoutenance is not null');

        $theses = array_filter($qb->getQuery()->getResult(), function (These $these) {
            return $these->getDateButoirDepotVersionCorrigeeDepassee();
        });

        return $theses;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return int[]
     * @see Year
     */
    public function fetchDistinctAnneesPremiereInscription(Etablissement $etablissement = null)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("year(t.datePremiereInscription)")
            ->orderBy("year(t.datePremiereInscription)");

        if ($etablissement !== null) {
            $qb
                ->join('t.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return int[]
     * @see Year
     */
    public function fetchDistinctAnneesSoutenance(Etablissement $etablissement = null)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("year(t.dateSoutenance)")
            ->orderBy("year(t.dateSoutenance)");

        if ($etablissement !== null) {
            $qb
                ->join('t.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }

    /**
     * @param Etablissement|null $etablissement
     * @return string[]
     */
    public function fetchDistinctDisciplines(Etablissement $etablissement = null)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->distinct()
            ->select("t.libelleDiscipline")
            ->orderBy("t.libelleDiscipline");

        if ($etablissement !== null) {
            $qb
                ->join('t.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }

    /**
     * @param Doctorant $doctorant
     * @return These[]
     */
    public function fetchThesesByDoctorant($doctorant)
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.doctorant = :doctorant')
            ->setParameter('doctorant', $doctorant)
            ->andWhere('t.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @return These[]
     */
    public function fetchThesesByEncadrant($individu)
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.acteurs', 'a')
            ->join('a.role', 'r')
            ->andWhere('r.code = :directeur OR r.code = :codirecteur')
            ->setParameter('directeur', Role::CODE_DIRECTEUR_THESE)
            ->setParameter('codirecteur', Role::CODE_CODIRECTEUR_THESE)
            ->andWhere('t.etatThese = :encours')
            ->setParameter('encours', These::ETAT_EN_COURS)
            ->andWhere('a.individu = :individu')
            ->setParameter('individu', $individu)
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }



}