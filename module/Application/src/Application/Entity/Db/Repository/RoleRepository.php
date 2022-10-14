<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Role;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\Etablissement;
use UnicaenApp\Exception\RuntimeException;

class RoleRepository extends DefaultEntityRepository
{
    /**
     * @param string $roleCode
     * @return Role|null
     */
    public function findByCode(string $roleCode): ?Role
    {
        /** @var Role $role */
        $role = $this->findOneBy(["code" => $roleCode]);

        return $role;
    }

    /**
     * @param string[] $rolesCodes
     * @return Role[]
     */
    public function findByCodes(array $rolesCodes): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->andWhere($qb->expr()->in('r.code', $rolesCodes))
            ->addSelect('s')
            ->leftJoin('r.structure', 's')
            ->addSelect('structureSubstituante')
            ->leftJoin('s.structureSubstituante', 'structureSubstituante')
            ->orderBy('r.libelle, structureSubstituante.libelle, s.libelle');

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche du rôle Doctorant pour un établissement.
     *
     * NB : ici pas de jointure vers l'éventuelle structure substituée, charge à l'appelant de passer la structure
     * substituée si besoin.
     *
     * @param string|Etablissement $etablissement
     * @return Role|null
     */
    public function findRoleDoctorantForEtab(Etablissement $etablissement): ?Role
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->addSelect('s')
            ->join('r.structure', 's', Join::WITH, 's.code = :etablissement')
            ->where('r.code = :code')
            ->setParameter('code', Role::CODE_DOCTORANT)
            ->setParameter('etablissement', $code = $etablissement->getStructure()->getCode());

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs rôles doctorant trouvés pour le même établissement " . $code);
        }
    }

    /**
     * Recherche de rôles par établissement.
     *
     * NB : ici pas de jointure vers l'éventuelle structure substituée, charge à l'appelant de passer la structure
     * substituée si besoin.
     *
     * @param Etablissement $etablissement
     * @return Role[]
     */
    public function findAllRolesTheseDependantByEtablissement(Etablissement $etablissement): array
    {
        $qb = $this->createQueryBuilder("role")
            ->andWhere("role.theseDependant = true")
            ->andWhere("role.structure = :etablissement")
            ->setParameter("etablissement", $etablissement)
            ->orderBy("role.ordreAffichage", "DESC");

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche d'un rôle selon son code et son établissement.
     *
     * NB : ici pas de jointure vers l'éventuelle structure substituée, charge à l'appelant de passer la structure
     * substituée si besoin.
     *
     * @param string $code
     * @param Etablissement $etablissement
     * @return Role|null
     */
    public function findByCodeAndEtablissement(string $code, Etablissement $etablissement): ?Role
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.structure = :structure')
            ->setParameter('structure', $etablissement->getStructure())
            ->andWhere('r.code = :code')
            ->setParameter('code', $code)
            ->andWhere('r.histoDestruction is null');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs Role partagent le même code ['.$code.'] et le même établissement ['.$etablissement->getStructure()->getCode().']');
        }
    }
}