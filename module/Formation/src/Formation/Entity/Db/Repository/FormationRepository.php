<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Formation\Entity\Db\Formation;
use Formation\Entity\Db\Module;
use Formation\Entity\Db\Session;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationRepository extends DefaultEntityRepository
{
    use EntityManagerAwareTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);
        $qb
            ->leftjoin($alias . '.module', 'module')->addSelect('module')
            ->leftJoin($alias . '.responsable', 'resp')->addSelect('resp')
            ->leftJoin($alias . '.site', 'site')->addSelect('site');

        $qb
            ->leftJoin('site.structure', 'site_structure')->addSelect('site_structure')
            ->leftJoin($alias . '.typeStructure', 'struct')->addSelect('struct');

        return $qb;
    }

    /**
     * @return Formation[]
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('f')->getQuery()->getResult();
    }

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

    /**
     * @param Module|null $module
     * @param string $champ
     * @param string $ordre
     * @param bool $keep_histo
     * @return array
     */
    public function fetchFormationsByModule(?Module $module, string $champ='libelle', string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQueryBuilder('formation')
            ->orderBy('formation.' . $champ, $ordre);

        if ($module !== null)   $qb = $qb->andWhere('formation.module = :module')->setParameter('module', $module);
        else                    $qb = $qb->andWhere('formation.module IS NULL');

        if (!$keep_histo) $qb = $qb->andWhere('formation.histoDestruction IS NULL');

        return $qb->getQuery()->getResult();
    }

    public function fetchDistinctAnneesUnivFormation(string $ordre='ASC', bool $keep_histo = false) : array
    {
        $qb = $this->createQueryBuilder('formation')
            ->leftJoin('formation.sessions', 'session')->addSelect('session')
            ->leftJoin('session.seances', 'seance')->addSelect('seance')
            ->distinct()
            ->select("YEAR(seance.debut) as annee")
            ->orderBy("annee", $ordre);

        if (!$keep_histo) $qb = $qb->andWhere('session.histoDestruction IS NULL');

        return array_map(function($value) {
            return current($value);
        }, $qb->getQuery()->getScalarResult());
    }
}