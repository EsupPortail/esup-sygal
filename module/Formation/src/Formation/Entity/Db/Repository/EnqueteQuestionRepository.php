<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Formation\Entity\Db\EnqueteQuestion;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

/**
 * @method DefaultQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class EnqueteQuestionRepository extends DefaultEntityRepository
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

    public function findAll()
    {
        $qb = $this->createQueryBuilder('eq')
            ->andWhereNotHistorise('eq')
            ->addOrderBy('eq.ordre', 'ASC');

        return $qb->getQuery()->getResult();
    }
}