<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class SessionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Session|null
     */
    public function getRequestedSession(AbstractActionController $controller, string $param = 'session') : ?Session
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Session|null $session */
        $session = $this->find($id);
        return $session;
    }

//    /**
//     * @return Formation[]
//     */
//    public function findAllByResponsable() : array
//    {
//
//    }
}