<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\UniteRechercheIndividu;
use Doctrine\ORM\Query\Expr\Join;

class UniteRechercheRepository extends DefaultEntityRepository
{
    /**
     * @param string $sourceCodeIndividu
     * @return UniteRechercheIndividu[]
     */
    public function findMembresBySourceCodeIndividu($sourceCodeIndividu)
    {
        $qb = $this->getEntityManager()->getRepository(UniteRechercheIndividu::class)->createQueryBuilder('uri');
        $qb
            ->addSelect('ur, i, r')
            ->join('uri.uniteRecherche', 'ur')
            ->join('uri.individu', 'i', Join::WITH, 'i.sourceCode = :sourceCode')
            ->join('uri.role', 'r')
            ->setParameter('sourceCode', $sourceCodeIndividu)
        ;

        return $qb->getQuery()->getResult();
    }
}