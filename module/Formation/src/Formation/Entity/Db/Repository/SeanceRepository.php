<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class SeanceRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Seance|null
     */
    public function getRequestedSeance(AbstractActionController $controller, string $param = 'seance') : ?Seance
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Seance|null $seance */
        $seance = $this->find($id);
        return $seance;
    }

//    /**
//     * @return Formation[]
//     */
//    public function findAllByResponsable() : array
//    {
//
//    }
}