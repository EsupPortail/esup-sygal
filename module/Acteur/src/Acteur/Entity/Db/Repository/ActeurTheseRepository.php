<?php

namespace Acteur\Entity\Db\Repository;

use Acteur\Entity\Db\ActeurThese;
use Application\Entity\Db\Role;
use Individu\Entity\Db\Individu;
use Soutenance\Entity\Membre;
use Structure\Entity\Db\EcoleDoctorale;
use Structure\Entity\Db\Etablissement;
use These\Entity\Db\These;

class ActeurTheseRepository extends AbstractActeurRepository
{
    public function findActeurByIndividuAndThese(Individu $individu, These $entity): ActeurThese|null
    {
        return parent::findActeurByIndividuAndEntity($individu, $entity);
    }

    public function findActeurByIndividuAndTheseAndRole(Individu $individu, These $entity, Role|string $role): ActeurThese|null
    {
        return parent::findActeurByIndividuAndEntityAndRole($individu, $entity, $role);
    }

    /**
     * @return ActeurThese[]
     */
    public function findActeurByThese(These $entity): array
    {
        return parent::findActeurByEntity($entity);
    }

    /**
     * @return ActeurThese[]
     */
    public function findActeursByTheseAndRole(These $entity, Role|string $role): array
    {
        return parent::findActeursByEntityAndRole($entity, $role);
    }

    /**
     * @return ActeurThese[]
     */
    public function findEncadrementThese(These $entity): array
    {
        return parent::findEncadrementEntity($entity);
    }

    /**
     * @return ActeurThese[]
     */
    public function findActeursForIndividu(Individu $individu): array
    {
        return parent::findActeursForIndividu($individu);
    }

    /**
     * Recherche d'acteurs selon leur rôle.
     *
     * @param Role|string $role Rôle, ou code du rôle (ex: {@see Role::CODE_DIRECTEUR_THESE})
     * @param Etablissement|null $etablissement Etablissement éventuel
     * @param EcoleDoctorale|string|array|null $ecoleDoctorale {@see EcoleDoctorale}, code structure de l'ED, ou ['s.sigle' => 'ED 591 NBISE'] par ex.
     * @return ActeurThese[]
     */
    public function findActeursByRole(
        Role|string $role,
        Etablissement|null $etablissement = null,
        EcoleDoctorale|string|array|null $ecoleDoctorale = null): array
    {
        return parent::findActeursByRole($role, $etablissement, $ecoleDoctorale);
    }

    /** Recupération des présidents de jurys **************************************************************************/

    /**
     * @return ActeurThese[]
     */
    public function findActeursPresidentDuJuryForThesesAvecCorrection(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('i')->join('a.individu', 'i')
            ->addSelect('t')->join('a.these', 't')
            ->addSelect('m')->leftJoin('a.membre', 'm')
            ->addSelect('r')->join('a.role', 'r')
            ->addSelect('s')->leftJoin('r.structure', 's')
            ->andWhere('r.code = :president')
            ->setParameter('president', Role::CODE_PRESIDENT_JURY)
            ->addSelect('u')->leftJoin('i.utilisateurs', 'u')
            ->andWhere("t.correctionAutorisee is not null or t.correctionAutoriseeForcee is not null")
            ->andWhere('a.histoDestruction is null')
            ->andWhere('i.histoDestruction is null')
            ->andWhere('t.histoDestruction is null')
            ->orderBy('t.dateSoutenance', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findActeurForSoutenanceMembre(Membre $soutenanceMembre): ActeurThese|null
    {
        return parent::findActeurForSoutenanceMembre($soutenanceMembre);
    }

    /**
     * @param Membre[] $rapporteurs
     * @return ActeurThese[]
     */
    public function findActeursForSoutenanceMembres(array $rapporteurs): array
    {
        return parent::findActeursForSoutenanceMembres($rapporteurs);
    }
}