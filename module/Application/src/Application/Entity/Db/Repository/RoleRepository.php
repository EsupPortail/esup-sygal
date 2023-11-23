<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Role;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\StructureConcreteInterface;
use Structure\Entity\Db\StructureInterface;
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
     * Recherche de rôles "thèse dépendants" liés à la structure *concrète* spécifiée.
     *
     * @param StructureConcreteInterface $structureConcrete
     * @return Role[]
     */
    public function findAllRolesTheseDependantForStructureConcrete(StructureConcreteInterface $structureConcrete): array
    {
        $qb = $this->createQueryBuilder("role")
            ->join('role.structure', 's')
            ->andWhere("role.theseDependant = true")
            ->andWhereStructureIs($structureConcrete->getStructure())
            ->orderBy("role.ordreAffichage", "DESC");

        return $qb->getQuery()->getResult();
    }

    /**
     * Recherche d'un rôle selon son code et la structure *concrète* liée (Etab, ED, UR).
     *
     * @param string $code
     * @param \Structure\Entity\Db\StructureConcreteInterface $structureConcrete
     * @return \Application\Entity\Db\Role|null
     */
    public function findOneByCodeAndStructureConcrete(string $code, StructureConcreteInterface $structureConcrete): ?Role
    {
        return $this->findOneByCodeAndStructure($code, $structureConcrete->getStructure(/*false*/));
    }

    /**
     * Recherche d'un rôle selon son code et la {@see \Structure\Entity\Db\Structure} *abstraite* liée.
     *
     * @param string $code
     * @param \Structure\Entity\Db\StructureInterface $structure
     * @return \Application\Entity\Db\Role|null
     */
    public function findOneByCodeAndStructure(string $code, StructureInterface $structure): ?Role
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.structure', 's')->addSelect('s')
            ->andWhere('r.code = :code')
            ->setParameter('code', $code)
            ->andWhereStructureIs($structure)
            ->andWhere('r.histoDestruction is null');

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs Role partagent le même code ['.$code.'] et la même structure ['.$structure->getId().']');
        }
    }
}