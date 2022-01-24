<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\Module;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

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

    /**
     * @param array $filtres
     * @return Module[]
     */
    public function fetchModulesWithFiltres(array $filtres) : array
    {
        $alias = 'module';
        $qb = $this->createQueryBuilder($alias);
        if ($filtres['libelle']) {
            $libelle = '%' . strtolower($filtres['libelle']) . '%';
            $qb = $qb->andWhere('lower('.$alias.'.libelle) like :libelle')
                ->setParameter('libelle', $libelle);
        }

        $result = $qb->getQuery()->getResult();
        return $result;
    }

}