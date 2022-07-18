<?php

namespace Formation\Entity\Db\Repository;

trait SeanceRepositoryAwareTrait
{
    protected SeanceRepository $seanceRepository;

    /**
     * @param \Formation\Entity\Db\Repository\SeanceRepository $seanceRepository
     */
    public function setSeanceRepository(SeanceRepository $seanceRepository): void
    {
        $this->seanceRepository = $seanceRepository;
    }
}

