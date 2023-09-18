<?php

namespace Substitution\Service;

trait ForeignKeyServiceAwareTrait
{
    protected ForeignKeyService $foreignKeyService;

    public function setForeignKeyService(ForeignKeyService $foreignKeyService): void
    {
        $this->foreignKeyService = $foreignKeyService;
    }
}