<?php

namespace Admission\Entity\Db\Repository;

trait EtatRepositoryAwareTrait
{
    protected EtatRepository $etatRepository;

    /**
     * @param EtatRepository $etatRepository
     */
    public function setEtatRepository(EtatRepository $etatRepository): void
    {
        $this->etatRepository = $etatRepository;
    }
}

