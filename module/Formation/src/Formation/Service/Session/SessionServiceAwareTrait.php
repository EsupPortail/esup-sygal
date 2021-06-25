<?php

namespace Formation\Service\Session;

trait SessionServiceAwareTrait
{
    /** @var SessionService */
    private $sessionService;

    /**
     * @return SessionService
     */
    public function getSessionService(): SessionService
    {
        return $this->sessionService;
    }

    /**
     * @param SessionService $sessionService
     * @return SessionService
     */
    public function setSessionService(SessionService $sessionService): SessionService
    {
        $this->sessionService = $sessionService;
        return $this->sessionService;
    }

}