<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Formation;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationRepository extends EntityRepository
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