<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Formation\Entity\Db\EnqueteQuestion;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Laminas\Mvc\Controller\AbstractActionController;

class EnqueteQuestionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return EnqueteQuestion|null
     */
    public function getRequestedEnqueteQuestion(AbstractActionController $controller, string $param = 'question') : ?EnqueteQuestion
    {
        $id = $controller->params()->fromRoute($param);
        /** @var EnqueteQuestion|null $question */
        $question = $this->find($id);
        return $question;
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