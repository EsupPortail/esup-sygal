<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\EcoleDoctoraleIndividu;
use Doctrine\ORM\Query\Expr\Join;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCodeIndividu
     * @return EcoleDoctoraleIndividu[]
     */
    public function findMembresBySourceCodeIndividu($sourceCodeIndividu)
    {
        $qb = $this->getEntityManager()->getRepository(EcoleDoctoraleIndividu::class)->createQueryBuilder('edi');
        $qb
            ->addSelect('ed, i, r')
            ->join('edi.ecole', 'ed')
            ->join('edi.individu', 'i', Join::WITH, 'i.sourceCode = :sourceCode')
            ->join('edi.role', 'r')
            ->setParameter('sourceCode', $sourceCodeIndividu)
        ;

        return $qb->getQuery()->getResult();
    }
}