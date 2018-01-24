<?php

namespace Application\Entity\Db\Repository;

use Application\QueryBuilder\ValidationQueryBuilder;

/**
 * @method ValidationQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class ValidationRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = ValidationQueryBuilder::class;

}