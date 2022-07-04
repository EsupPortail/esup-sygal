<?php

namespace Formation\Entity\Db\Repository;

trait EtatRepositoryAwareTrait
{
    protected EtatRepository $etatRepository;

    /**
     * @param \Formation\Entity\Db\Repository\EtatRepository $etatRepository
     */
    public function setEtatRepository(EtatRepository $etatRepository): void
    {
        $this->etatRepository = $etatRepository;
    }
}

