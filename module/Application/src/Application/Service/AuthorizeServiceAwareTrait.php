<?php

namespace Application\Service;

use UnicaenAuth\Service\AuthorizeService;

trait AuthorizeServiceAwareTrait
{
    /**
     * @var AuthorizeService
     */
    private $authorizeService;

    /**
     * @param AuthorizeService $authorizeService
     * @return self
     */
    public function setAuthorizeService(AuthorizeService $authorizeService)
    {
        $this->authorizeService = $authorizeService;

        return $this;
    }
}