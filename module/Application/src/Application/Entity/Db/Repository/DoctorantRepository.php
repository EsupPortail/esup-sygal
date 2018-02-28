<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Doctrine\ORM\NonUniqueResultException;

class DoctorantRepository extends DefaultEntityRepository
{
    /**
     * @param $username
     * @return Doctorant
     * @throws NonUniqueResultException
     */
    public function findOneByUsername($username)
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->leftJoin('t.complements', 'c')
            ->andWhere('1 = pasHistorise(t)')
            ->andWhere('t.sourceCode like :sourceCode OR t.sourceCode = :login OR c.persopass = :login')
            ->setParameter('sourceCode', '%::' . $username)
            ->setParameter('login', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }
}