<?php

namespace HDR\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\Entity\Db\Role;
use Application\ORM\Query\Functions\Year;
use Candidat\Entity\Db\Candidat;
use Doctrine\ORM\Query\Expr\Join;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use Structure\Entity\Db\Etablissement;

class HDRRepository extends DefaultEntityRepository
{
    
    /**
     * @param Etablissement|null $etablissement
     * @return int[]
     * @see Year
     */
    public function fetchDistinctAnneesSoutenance(Etablissement $etablissement = null)
    {
        $qb = $this->createQueryBuilder('hdr');
        $qb
            ->addSelect('propositions')->join('hdr.propositionsHDR', 'propositions')
            ->distinct()
            ->select("year(propositions.date)")
            ->orderBy("year(propositions.date)");

        if ($etablissement !== null) {
            $qb
                ->join('hdr.etablissement', 'etab', Join::WITH, 'etab = :etablissement')
                ->setParameter('etablissement', $etablissement);
        }

        $results = array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());

        return $results;
    }

    /**
     * @param Candidat $candidat
     * @param string[] $etats
     * @return HDR[]
     */
    public function findHDRByCandidat(Candidat $candidat, array $etats = [HDR::ETAT_EN_COURS]): array
    {
        return $this->findHDRByCandidatAsIndividu($candidat->getIndividu(), $etats);
    }

    /**
     * @param Individu $individu
     * @param array $etats
     * @return HDR[]
     */
    public function findHDRByCandidatAsIndividu(Individu $individu, array $etats = [HDR::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('hdr')
            ->join("hdr.candidat", "c")
            ->join('c.individu', 'i')
            ->andWhere('i = :individu')
            ->setParameter('individu', $individu)
            ->andWhere('hdr.histoDestruction is null')
        ;

        if ($etats) {
            $qb->andWhere($qb->expr()->in("hdr.etatHDR", $etats));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @param array $etats
     * @return HDR[]
     */
    public function findHDRByActeur(Individu $individu, Role $role, array $etats = [HDR::ETAT_EN_COURS]): array
    {
        $qb = $this->createQueryBuilder('hdr')
            ->join('hdr.acteurs', 'a')
            ->andWhere('a.individu = :individu')->setParameter('individu', $individu)
            ->andWhere('a.role = :role')->setParameter('role', $role)->andWhereNotHistorise('a')
            ->andWhereNotHistorise();
        $qb->andWhere($qb->expr()->in("hdr.etatHDR", $etats));

        return $qb->getQuery()->getResult();
    }
}