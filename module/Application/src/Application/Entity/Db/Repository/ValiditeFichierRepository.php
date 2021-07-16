<?php

namespace Application\Entity\Db\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * @method QueryBuilder createQueryBuilder()
 */
class ValiditeFichierRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = QueryBuilder::class;

}