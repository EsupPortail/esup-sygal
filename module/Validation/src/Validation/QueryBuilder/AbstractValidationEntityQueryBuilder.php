<?php

namespace Validation\QueryBuilder;

use Application\QueryBuilder\DefaultQueryBuilder;
use Validation\Entity\Db\TypeValidation;

class AbstractValidationEntityQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "v";

    public function initWithDefault(): static
    {
        $this
            ->addSelect("val, tv, i")
            ->join("$this->rootAlias.validation", "val")
            ->join("val.typeValidation", 'tv')
            ->leftJoin("$this->rootAlias.individu", "i")
        ;

        return $this;
    }

    public function andWhereTypeIs(TypeValidation|string $typeValidation): static
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