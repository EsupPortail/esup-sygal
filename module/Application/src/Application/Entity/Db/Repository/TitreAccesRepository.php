<?php

namespace Application\Entity\Db\Repository;

class TitreAccesRepository extends DefaultEntityRepository
{
    /**
     * Récupérer les types distincts d'établissement (typeEtabTitreAcces)
     */
    public function findDistinctTypeEtabTitreAcces()
    {
        return $this->createQueryBuilder('ta')
            ->select('DISTINCT ta.typeEtabTitreAcces')
            ->where('ta.typeEtabTitreAcces IS NOT NULL')
            ->addOrderBy('ta.typeEtabTitreAcces', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
