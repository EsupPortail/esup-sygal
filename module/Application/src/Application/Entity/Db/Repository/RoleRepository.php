<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class RoleRepository extends DefaultEntityRepository
{

//    /**
//     * @param int $id
//     * @return Role
//     */
//    public function find($id) {
//
//        /** @var Role $role */
//        $role = $this->findOneBy(["id" => $id]);
//        return $role;
//    }

    /**
     * @param string $roleCode
     * @return Role
     */
    public function findByCode($roleCode) {

        /** @var Role $role */
        $role = $this->findOneBy(["code" => $roleCode]);
        return $role;
    }

    /**
     * @param string[] $rolesCodes
     * @return Role[]
     */
    public function findByCodes(array $rolesCodes)
    {
        /** @var Role $role */
        $qb = $this->createQueryBuilder('r');
        $qb
            ->andWhere($qb->expr()->in('r.code', $rolesCodes))
            ->leftJoin('r.structure', 's')
            ->orderBy('r.libelle, s.libelle');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string|Etablissement $etablissement
     * @return Role
     */
    public function findRoleDoctorantForEtab($etablissement)
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getStructure()->getCode();
        }

        $qb = $this->createQueryBuilder('r');
        $qb
            ->addSelect('s')
            ->join('r.structure', 's', Join::WITH, 's.code = :etablissement')
            ->where('r.code = :code')
            ->setParameter('code', Role::CODE_DOCTORANT)
            ->setParameter('etablissement', $etablissement)
            ;

        try {
            $role = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs rôles doctorant trouvés pour le même établissement " . $etablissement);
        }

        return $role;
    }

    /**
     * @param $individu
     * @return Role[]
     */
    public function findAllByIndividu($individu)
    {
        $qb = $this->getEntityManager()->getRepository(IndividuRole::class)->createQueryBuilder("ir")
            ->andWhere("ir.individu = :individu")
            ->setParameter("individu", $individu);
        $results = $qb->getQuery()->getResult();

        /** @var IndividuRole $result */
        $roles = [];
        foreach($results as $result) {
            $roles[] = $result->getRole();
        }

        return $roles;
    }

    /**
     * @param Etablissement $etablissement
     * @return Role[]
     */
    public function findAllRolesTheseDependantByEtablissement($etablissement)
    {
        $qb = $this->createQueryBuilder("role")
            ->andWhere("role.theseDependant = 1")
            ->andWhere("role.structure = :etablissement")
            ->setParameter("etablissement", $etablissement)
            ->orderBy("role.ordreAffichage", "DESC")
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $code
     * @param Etablissement $etablissement
     * @return Role
     */
    public function findByCodeAndEtablissement(string $code, Etablissement $etablissement)
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.structure = :structure')
            ->setParameter('structure', $etablissement->getStructure())
            ->andWhere('r.code = :code')
            ->setParameter('code', $code)
            ->andWhere('1 = pasHistorise(r)')
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException('Plusieurs Role partagent le même code ['.$code.'] et le même établissement ['.$etablissement->getStructure()->getCode().']');
        }
        return $result;
    }
}