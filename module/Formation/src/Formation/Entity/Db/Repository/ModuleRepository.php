<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Module;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class ModuleRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Module|null
     */
    public function getRequestedModule(AbstractActionController $controller, string $param = 'module') : ?Module
    {
        $module = null;
        $id = $controller->params()->fromRoute($param);
        /** @var Module|null $module */
        if ($id) {
            $module = $this->find($id);
        }
        return $module;

    }

    public function createQB() : QueryBuilder
    {
        $qb = $this->createQueryBuilder('module')
            ->leftjoin('module.formations', 'formation')->addSelect('formation');
        return $qb;
    }

    public function getModules(string $champ = "libelle", $ordre='ASC')
    {
        $qb = $this->createQB()
            ->orderBy("module.".$champ, $ordre);
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}