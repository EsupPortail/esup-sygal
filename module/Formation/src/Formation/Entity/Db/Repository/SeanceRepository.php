<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Seance;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SeanceRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

//    /**
//     * @return Formation[]
//     */
//    public function findAllByResponsable() : array
//    {
//
//    }
}