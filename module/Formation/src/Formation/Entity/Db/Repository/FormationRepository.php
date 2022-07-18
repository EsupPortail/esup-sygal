<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Module;
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

    public function createQB() : QueryBuilder
    {
        $qb = $this->createQueryBuilder('formation')
            ->leftjoin('formation.module', 'module')->addSelect('module');
        return $qb;
    }

    /**
     * @param Module|null $module
     * @param string $champ
     * @param string $ordre
     * @param bool $keep_histo
     * @return array
     */
    public function fetchFormationsByModule(?Module $module, string $champ='libelle', string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQB()
            ->orderBy('formation.' . $champ, $ordre);

        if ($module !== null)   $qb = $qb->andWhere('formation.module = :module')->setParameter('module', $module);
        else                    $qb = $qb->andWhere('formation.module IS NULL');

        if (!$keep_histo) $qb = $qb->andWhere('formation.histoDestruction IS NULL');

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}