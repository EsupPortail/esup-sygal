<?php

namespace Validation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;

class AbstractValidationEntityRepository extends DefaultEntityRepository
{
    protected string $queryBuilderClassName;
}