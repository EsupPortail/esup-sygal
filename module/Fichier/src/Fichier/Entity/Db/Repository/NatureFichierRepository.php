<?php

namespace Fichier\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;

class NatureFichierRepository extends DefaultEntityRepository
{
    public function findAll()
    {
        return $this->createQueryBuilder('nf')
            ->orderBy('nf.code')
            ->indexBy('nf', 'nf.code')
            ->getQuery()->getResult();
    }
}