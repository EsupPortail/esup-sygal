<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Etablissement;
use Application\Entity\Db\Role;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class RoleRepository extends DefaultEntityRepository
{
    /**
     * @param string|Etablissement $etablissement
     * @return Role
     */
    public function findRoleDoctorantForEtab($etablissement)
    {
        if ($etablissement instanceof Etablissement) {
            $etablissement = $etablissement->getCode();
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
}