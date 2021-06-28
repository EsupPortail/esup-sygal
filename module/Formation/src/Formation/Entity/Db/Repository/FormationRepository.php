<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Formation;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class FormationRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Formation|null
     */
    public function getRequestedFormation(AbstractActionController $controller, string $param = 'formation') : ?Formation
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Formation|null $formation */
        $formation = $this->find($id);
        return $formation;
    }

//    /**
//     * @return Formation[]
//     */
//    public function findAllByResponsable() : array
//    {
//
//    }
}