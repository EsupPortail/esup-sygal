<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class InscriptionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Inscription|null
     */
    public function getRequestedInscription(AbstractActionController $controller, string $param = 'inscription') : ?Inscription
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Inscription|null $inscription */
        $inscription = $this->find($id);
        return $inscription;
    }

}