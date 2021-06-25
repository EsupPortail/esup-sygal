<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionRepository extends EntityRepository
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