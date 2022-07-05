<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Formateur;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class FormateurRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Formateur|null
     */
    public function getRequestedFormateur(AbstractActionController $controller, string $param = 'formateur') : ?Formateur
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Formateur|null $formateur */
        $formateur = $this->find($id);
        return $formateur;
    }

}