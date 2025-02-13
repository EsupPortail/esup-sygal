<?php

namespace Validation\QueryBuilder;

use Application\QueryBuilder\DefaultQueryBuilder;
use Validation\Entity\Db\TypeValidation;

class ValidationQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "v";

    public function initWithDefault(): self
    {
        $this
            ->addSelect("tv")
            ->join("$this->rootAlias.typeValidation", 'tv')
        ;

        return $this;
    }

    public function andWhereTypeIs($typeValidation): static
    {
        if ($typeValidation instanceof TypeValidation) {
            $typeValidation = $typeValidation->getCode();
        }

        $this
            ->andWhere('tv.code = :tvcode')
            ->setParameter('tvcode', $typeValidation);

        return $this;
    }
}