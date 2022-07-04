<?php

namespace Formation\Entity\Db\Repository;

trait PresenceRepositoryAwareTrait
{
    protected PresenceRepository $presenceRepository;

    /**
     * @param \Formation\Entity\Db\Repository\PresenceRepository $presenceRepository
     */
    public function setPresenceRepository(PresenceRepository $presenceRepository): void
    {
        $this->presenceRepository = $presenceRepository;
    }
}

