<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Acteur;
use Application\Entity\Db\Individu;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
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

    public function findActeurByIndividuAndThese($individuId, $these)
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhereNotHistorise()
            ->andWhere('a.individu = :individu')
            ->andWhere('a.these = :these')
            ->setParameter('individu', $individuId)
            ->setParameter('these', $these)
            ->orderBy('a.id', 'DESC')
        ;

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
            ->setParameter('these', $these->getId())
        ;

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
            ->orderBy('a.id', 'DESC')
        ;

        $acteur = null;
        try {
            $acteur = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la récupération de l'acteur.",$e);
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
            ->andWhere('1 = pasHistorise(acteur)')
        ;

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
            ->andWhere('1 = pasHistorise(acteur)')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }


}