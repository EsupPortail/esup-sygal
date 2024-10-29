<?php

namespace Formation\Service\Session\Search;

trait SessionSearchServiceAwareTrait
{
    private SessionSearchService $sessionSearchService;

    /**
     * @return SessionSearchService
     */
    public function getSessionSearchService(): SessionSearchService
    {
        return $this->sessionSearchService;
    }

    /**
     * @param SessionSearchService $sessionSearchService
     * @return SessionSearchService
     */
    public function setSessionSearchService(SessionSearchService $sessionSearchService): SessionSearchService
    {
        $this->sessionSearchService = $sessionSearchService;
        return $this->sessionSearchService;
    }

}