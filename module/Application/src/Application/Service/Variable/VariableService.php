<?php

namespace Application\Service\Variable;

use Application\Entity\Db\Repository\VariableRepository;
use Application\Entity\Db\Variable;
use Application\Service\BaseService;

/**
 * @method Variable|null findOneBy(array $criteria, array $orderBy = null)
 */
class VariableService extends BaseService
{
    /**
     * @return VariableRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Variable::class);
    }
}