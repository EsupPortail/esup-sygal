<?php

namespace Formation\Entity\Db\Repository;

trait SessionRepositoryAwareTrait
{
    protected SessionRepository $sessionRepository;

    /**
     * @param \Formation\Entity\Db\Repository\SessionRepository $sessionRepository
     */
    public function setSessionRepository(SessionRepository $sessionRepository): void
    {
        $this->sessionRepository = $sessionRepository;
    }
}

