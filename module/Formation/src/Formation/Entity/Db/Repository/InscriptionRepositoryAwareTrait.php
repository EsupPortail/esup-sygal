<?php

namespace Formation\Entity\Db\Repository;

trait InscriptionRepositoryAwareTrait
{
    protected InscriptionRepository $inscriptionRepository;

    /**
     * @param \Formation\Entity\Db\Repository\InscriptionRepository $inscriptionRepository
     */
    public function setInscriptionRepository(InscriptionRepository $inscriptionRepository): void
    {
        $this->inscriptionRepository = $inscriptionRepository;
    }
}

