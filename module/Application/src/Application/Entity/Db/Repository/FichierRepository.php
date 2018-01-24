<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\FichierQueryBuilder;

/**
 * @method FichierQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class FichierRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = FichierQueryBuilder::class;

}