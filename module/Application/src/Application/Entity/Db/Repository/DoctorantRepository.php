<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param string        $sourceCode
     * @return Doctorant
     * @throws NonUniqueResultException
     */
    public function findOneBySourceCode($sourceCode)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->addSelect('i')
            ->join('t.individu', 'i')
            ->where('t.sourceCode = :sourceCode')
            ->andWhere('1 = pasHistorise(t)')
            ->setParameter('sourceCode', $sourceCode);

        return $qb->getQuery()->getOneOrNullResult();
    }
}