<?php

namespace Formation\Service\Session;

trait SessionServiceAwareTrait
{
    private SessionService $sessionService;

    /**
     * @return SessionService
     */
    public function getSessionService(): SessionService
    {
        return $this->sessionService;
    }

    /**
     * @param SessionService $sessionService
     */
    public function setSessionService(SessionService $sessionService): void
    {
        $this->sessionService = $sessionService;
    }

}