<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Acteur;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use Individu\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

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
            ->andWhere('a.histoDestruction is null')
            ->setParameter('sourceCode', $sourceCodeIndividu);

        return $qb->getQuery()->getResult();
    }

    public function findActeurByIndividuAndThese($individuId, $these)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise()
            ->andWhere('a.individu = :individu')
            ->andWhere('a.these = :these')
            ->setParameter('individu', $individuId)
            ->setParameter('these', $these)
            ->orderBy('a.id', 'DESC');

        $acteurs = $qb->getQuery()->getResult();
        return current($acteurs);
    }

    /**
     * @param These $these
     * @return Acteur[]
     */
    public function findActeurByThese($these)
    {
        $qb = $this->createQueryBuilder('acteur')
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these->getId());

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Individu $individu
     * @param Role $role
     * @return Acteur
     */
    public function findActeurByIndividuAndRole($individu, $role)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise()
            ->andWhere('a.individu = :individu')
            ->andWhere('a.role = :role')
            ->setParameter('individu', $individu)
            ->setParameter('role', $role)
            ->orderBy('a.id', 'DESC');

        $acteur = null;
        try {
            $acteur = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de l'acteur.", $e);
        }
        return $acteur;
    }

    /**
     * @param These $these
     * @param Role|string $role
     * @return Acteur[]
     */
    public function findActeursByTheseAndRole(These $these, $role)
    {
        $code = $role;
        if ($role instanceof Role) $code = $role->getCode();
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('role.code = :code')
            ->setParameter('code', $code)
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these)
            ->andWhere('acteur.histoDestruction is null');

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param These $these
     * @return Acteur[]
     */
    public function findEncadrementThese(These $these)
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('individu')->join('acteur.individu', 'individu')
            ->addSelect('role')->join('acteur.role', 'role')
            ->andWhere('role.code = :directeur OR role.code = :codirecteur')
            ->setParameter('directeur', Role::CODE_DIRECTEUR_THESE)
            ->setParameter('codirecteur', Role::CODE_CODIRECTEUR_THESE)
            ->andWhere('acteur.these = :these')
            ->setParameter('these', $these)
            ->andWhere('acteur.histoDestruction is null');

        $result = $qb->getQuery()->getResult();
        return $result;
    }


    /**
     * @param Individu $individu
     * @return Acteur[]
     */
    public function findActeursByIndividu(Individu $individu)
    {
        $qb = $this->createQueryBuilder('acteur')
            ->addSelect('these')->join('acteur.these', 'these')
            ->andWhere('acteur.histoDestruction is null')
            ->andWhere('acteur.individu = :individu')
            ->setParameter('individu', $individu)
            ->orderBy('these.id', 'ASC');

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
            ->andWhere('a.histoDestruction is null')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1');

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
            ->andWhere('a.histoDestruction is null')
            ->addOrderBy('i.nomUsuel')
            ->addOrderBy('i.prenom1');

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

    /** Recupération des présidents de jurys **************************************************************************/

    /**
     * @return Acteur[]
     */
    public function fetchPresidentDuJury()
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')->join('a.individu', 'i')
            ->addSelect('t')->join('t.these', 't')
            ->andWhere('a.complement = :president')
            ->setParameter('president', 'Président du jury')
            ->andWhere('a.histoDestruction is null')
            ->andWhere('i.histoDestruction is null')
            ->andWhere('t.histoDestruction is null');

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @return Acteur[]
     */
    public function fetchPresidentDuJuryTheseAvecCorrection()
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')->join('a.individu', 'i')
            ->addSelect('t')->join('a.these', 't')
            ->addSelect('r')->join('a.role', 'r')
            ->andWhere('r.code = :president')
            ->setParameter('president', Role::CODE_PRESIDENT_JURY)
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->andWhere("t.correctionAutorisee is not null or t.correctionAutoriseeForcee is not null")
            ->andWhere('a.histoDestruction is null')
            ->andWhere('i.histoDestruction is null')
            ->andWhere('t.histoDestruction is null')
            ->orderBy('t.dateSoutenance', 'DESC')
            //->orderBy('t.id', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}