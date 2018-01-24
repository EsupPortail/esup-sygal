<?php

namespace Application\Service;

trait UserContextServiceAwareTrait
{
    /**
     * @var UserContextService
     */
    protected $userContextService;

    /**
     * @param UserContextService $userContextService
     * @return self
     */
    public function setUserContextService(UserContextService $userContextService)
    {
        $this->userContextService = $userContextService;

        return $this;
    }
}