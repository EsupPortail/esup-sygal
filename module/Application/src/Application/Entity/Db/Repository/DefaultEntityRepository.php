<?php

namespace Application\Entity\Db\Repository;

use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\EntityRepository;

class DefaultEntityRepository extends EntityRepository
{
    /**
     * @var string
     */
    protected string $queryBuilderClassName = DefaultQueryBuilder::class;

    /**
     * @param string $alias
     * @param string $indexBy
     * @return DefaultQueryBuilder
     */
    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $className = $this->queryBuilderClassName;

        /** @var DefaultQueryBuilder $qb */
        $qb = new $className($this->_em, $alias);
        $qb
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy);

        if (method_exists($qb, $method = 'initWithDefault')) {
            call_user_func([$qb, $method]);
        }

        return $qb;
    }

}