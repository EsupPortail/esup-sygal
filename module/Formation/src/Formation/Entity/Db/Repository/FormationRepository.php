<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Session;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

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

    public function fetchIndexMax(Formation $module) : int
    {
        $index = 0;
        /** @var Session $session */
        foreach ($module->getSessions() as $session) {
            $index = max($session->getIndex(), $index);
        }
        return $index;
    }

    public function fetchListeResponsable() : array
    {
        /** @var Formation[] $modules */
        $modules = $this->findAll();
        $responsables = [];

        foreach ($modules as $module) {
            $responsable = $module->getResponsable();
            if ($responsable) {
                $responsables[$responsable->getId()] = $responsable;
            }
        }
        return $responsables;
    }

    public function fetchListeStructures() : array
    {
        /** @var Formation[] $modules */
        $modules = $this->findAll();
        $structures = [];

        foreach ($modules as $module) {
            $structure = $module->getTypeStructure();
            if ($structure) {
                $structures[$structure->getId()] = $structure;
            }
        }
        return $structures;
    }
}