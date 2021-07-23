<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Etat;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class EtatRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Etat|null
     */
    public function getRequestedEtat(AbstractActionController $controller, string $param = 'etat') : ?Etat
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Etat|null */
        $etat = $this->findOneBy(["code" => $id]);
        return $etat;
    }

}