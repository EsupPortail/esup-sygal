<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\StructureConcreteInterface;
use Application\Entity\Db\These;
use Application\Entity\Db\UniteRecherche;
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
            ->andWhereEtatIn([These::ETAT_EN_COURS])
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
     * @param string[] $etats
     * @return These[]
     */
    public function findThesesByDoctorant(Doctorant $doctorant, array $etats = [These::ETAT_EN_COURS]): array
    {
        return $this->findThesesByDoctorantAsIndividu($doctorant->getIndividu(), $etats);
    }

    /**
     * @param Individu $individu
     * @param array $etats
     * @return These[]
     */
    public function findThesesByDoctorantAsIndividu(Individu $individu, array $etats = [These::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('th.individu', 'i')
            ->andWhere('i = :individu')
            ->setParameter('individu', $individu)
            ->andWhereEtatIn($etats)
            ->andWhere('1 = pasHistorise(t)')
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @param array $etats
     * @return These[]
     */
    public function findThesesByActeur(Individu $individu, Role $role, array $etats = [These::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.acteurs', 'a')
            ->andWhere('a.individu = :individu')->setParameter('individu', $individu)
            ->andWhere('a.role = :role')->setParameter('role', $role)
            ->andWhereEtatIn($etats)
            ->orderBy('t.datePremiereInscription', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @param array $etats
     * @return These[]
     */
    public function fetchThesesByEncadrant(Individu $individu, array $etats = [These::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.acteurs', 'a')
            ->join('a.role', 'r')
            ->andWhere('r.code = :directeur OR r.code = :codirecteur')
            ->setParameter('directeur', Role::CODE_DIRECTEUR_THESE)
            ->setParameter('codirecteur', Role::CODE_CODIRECTEUR_THESE)
            ->andWhereEtatIn($etats)
            ->andWhere('a.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('1 = pasHistorise(t)')
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return These[]
     */
    public function fetchThesesByCoEncadrant($individu)
    {
        $qb = $this->createQueryBuilder('t')
            ->join('t.acteurs', 'a')
            ->join('a.role', 'r')
            ->andWhere('r.code = :coencadrant')
            ->setParameter('coencadrant', Role::CODE_CO_ENCADRANT)
            ->andWhere('a.individu = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('1 = pasHistorise(t)')
            ->orderBy('t.datePremiereInscription', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param EcoleDoctorale $structure
     * @param array $etats
     * @return These[]
     */
    public function findThesesForStructure(StructureConcreteInterface $structure, array $etats = [These::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('t');

        switch (true) {
            case $structure instanceof UniteRecherche :
                $qb
                    ->join('t.uniteRecherche', 'ur')
                    ->andWhere('ur = :unite')
                    ->setParameter('unite', $structure);
                break;
            case $structure instanceof EcoleDoctorale :
                $qb
                    ->join('t.ecoleDoctorale', 'ed')
                    ->andWhere('ed = :ecole')
                    ->setParameter('ecole', $structure);
                break;
            case $structure instanceof Etablissement :
                $qb
                    ->join('t.etablissement', 'e')
                    ->andWhere('e = :etablissement')
                    ->setParameter('etablissement', $structure);
                break;
            default:
                break;
        }

        $qb->andWhereEtatIn($etats);

        return $qb->getQuery()->getResult();
    }
}