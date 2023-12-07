<?php

namespace Substitution\Service\ForeignKey;

use Substitution\Service\ForeignKey\ForeignKeyService;

trait ForeignKeyServiceAwareTrait
{
    protected ForeignKeyService $foreignKeyService;

    public function setForeignKeyService(ForeignKeyService $foreignKeyService): void
    {
        $this->foreignKeyService = $foreignKeyService;
    }
}