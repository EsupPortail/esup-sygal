<?php

namespace Formation\Entity\Db\Repository;

trait FormateurRepositoryAwareTrait
{
    protected FormateurRepository $formateurRepository;

    /**
     * @param \Formation\Entity\Db\Repository\FormateurRepository $formateurRepository
     */
    public function setFormateurRepository(FormateurRepository $formateurRepository): void
    {
        $this->formateurRepository = $formateurRepository;
    }
}

