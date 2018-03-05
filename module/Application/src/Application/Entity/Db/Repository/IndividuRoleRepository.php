<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\Role;
use Doctrine\ORM\Query\Expr\Join;

class IndividuRoleRepository extends DefaultEntityRepository
{
    /**
     * @param Role role;
     * @return IndividuRole[]
     */
    public function findMembresByRole($role)
    {
        $qb = $this->getEntityManager()->getRepository(IndividuRole::class)->createQueryBuilder('ir');
        $qb
            ->addSelect('i, r')
            ->join('Role', 'r', Join::WITH, 'ir.role = :role')
            ->setParameter('role', $role);
        ;

        return $qb->getQuery()->getResult();
    }

//    public function findMembresByStructure($structure) {
//
//    }

}