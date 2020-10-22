<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;

class ActeurRepository extends DefaultEntityRepository
{
    /**
     * Recherche des Acteur tels que "individu.sourceCode = $sourceCodeIndividu".
     *
     * @param string $sourceCodeIndividu
     * @return Acteur[]
     */
    public function findBySourceCodeIndividu($sourceCodeIndividu)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('r')
            ->join('a.individu', 'i', Join::WITH, 'i.sourceCode = :sourceCode')
            ->join('a.role', 'r')
            ->setParameter('sourceCode', $sourceCodeIndividu);

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche des Acteur tels que "individu.sourceCode LIKE $sourceCodePattern".
     *
     * @param string $sourceCodePattern
     * @return Acteur[]
     */
    public function findBySourceCodeIndividuPattern($sourceCodePattern)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('r')
            ->join('a.individu', 'i', Join::WITH, 'i.sourceCode like :sourceCode')
            ->join('a.role', 'r')
            ->setParameter('sourceCode', '%::' . $sourceCodePattern);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Individu $individu
     * @return Acteur[]
     */
    public function findActeursByIndividu(Individu $individu)
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->andWhere('1 =  pasHistorise(acteur)')
            ->andWhere('acteur.individu = :individu')
            ->setParameter('individu', $individu)
            ->orderBy('these.id', 'ASC')
            ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Role|string $role Rôle, ou code du rôle (ex: {@see Role::CODE_DIRECTEUR_THESE})
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param string $etatThese Par défaut {@see These::ETAT_EN_COURS]
     * @return Acteur[]
     */
    public function findActeursByRoleAndEtab(
        $role,
        Etablissement $etablissement = null,
        $etatThese = These::ETAT_EN_COURS)
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')
            ->join('a.individu', 'i')
            ->join('a.role', 'r', Join::WITH, 'r.code = :role')->setParameter('role', $role)
            ->join('a.these', 't', Join::WITH, 't.etatThese = :etat')->setParameter('etat', $etatThese)
            ->join('t.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('1 =  pasHistorise(a)')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1')
        ;

        if ($etablissement !== null) {
            $qb->join('t.etablissement', 'e', Join::WITH, 'e = :etab')->setParameter('etab', $etablissement);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Role|string $role Rôle, ou code du rôle (ex: {@see Role::CODE_DIRECTEUR_THESE})
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param EcoleDoctorale|string|array|null $ecoleDoctorale {@see EcoleDoctorale}, code structure de l'ED, ou ['s.sigle' => 'ED 591 NBISE'] par ex.
     * @param string $etatThese Par défaut {@see These::ETAT_EN_COURS]
     * @return Acteur[]
     */
    public function findActeursByRole(
        $role,
        Etablissement $etablissement = null,
        $ecoleDoctorale = null,
        $etatThese = These::ETAT_EN_COURS)
    {
        if ($role instanceof Role) {
            $role = $role->getCode();
        }

        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')
            ->join('a.individu', 'i')
            ->join('a.role', 'r', Join::WITH, 'r.code = :role')->setParameter('role', $role)
            ->join('a.these', 't', Join::WITH, 't.etatThese = :etat')->setParameter('etat', $etatThese)
            ->join('t.ecoleDoctorale', 'ed')
            ->join('ed.structure', 's')
            ->andWhere('1 =  pasHistorise(a)')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1')
        ;

        if ($ecoleDoctorale !== null) {
            if ($ecoleDoctorale instanceof EcoleDoctorale) {
                $qb->andWhere('ed = :ed');
            } elseif (is_array($ecoleDoctorale)) {
                $qb->andWhere(key($ecoleDoctorale) . ' = :ed');
                $ecoleDoctorale = current($ecoleDoctorale);
            } else {
                $qb->andWhere('s.code = :ed');
            }
            $qb->setParameter('ed', $ecoleDoctorale);
        }

        if ($etablissement !== null) {
            $qb->join('t.etablissement', 'e', Join::WITH, 'e = :etab')->setParameter('etab', $etablissement);
        }

        return $qb->getQuery()->getResult();
    }
}