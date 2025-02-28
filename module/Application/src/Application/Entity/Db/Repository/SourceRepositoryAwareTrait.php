<?php

namespace Application\Entity\Db\Repository;

trait SourceRepositoryAwareTrait
{
    protected SourceRepository $sourceRepository;

    public function setSourceRepository(SourceRepository $service): void
    {
        $this->sourceRepository = $service;
    }
}