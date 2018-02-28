<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Acteur;
use Doctrine\ORM\Query\Expr\Join;

class ActeurRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCodeIndividu
     * @return Acteur[]
     */
    public function findBySourceCodeIndividu($sourceCodeIndividu)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->addSelect('r')
            ->join('a.individu', 'i', Join::WITH, 'i.sourceCode like :sourceCode')
            ->join('a.role', 'r')
            ->setParameter('sourceCode', '%::' . $sourceCodeIndividu);

        return $qb->getQuery()->getResult();
    }
}