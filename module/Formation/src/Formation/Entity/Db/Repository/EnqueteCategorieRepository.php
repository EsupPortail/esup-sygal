<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\EnqueteQuestion;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class EnqueteCategorieRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return EnqueteCategorie|null
     */
    public function getRequestedEnqueteCategorie(AbstractActionController $controller, string $param = 'categorie') : ?EnqueteCategorie
    {
        $id = $controller->params()->fromRoute($param);
        /** @var EnqueteCategorie|null $categorie */
        $categorie = $this->find($id);
        return $categorie;
    }

//    /**
//     * @param string $alias
//     * @return QueryBuilder
//     */
//    public function createQB(string $alias) : QueryBuilder
//    {
//        $qb = $this->createQueryBuilder($alias)
//            ->join($alias.".module", "module")->addSelect("module")
//            ->leftJoin($alias.".inscriptions", "inscription")->addSelect("inscription")
//            ->leftJoin($alias.".seances", "seance")->addSelect("seance")
//            ->leftJoin($alias.".etat", "etat")->addSelect("etat")
//        ;
//        return $qb;
//    }
//

}