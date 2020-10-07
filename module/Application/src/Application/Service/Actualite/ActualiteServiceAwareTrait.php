<?php

namespace Application\Service\Actualite;

trait ActualiteServiceAwareTrait
{
    /**
     * @var ActualiteService
     */
    protected $actualiteService;

    /**
     * @param ActualiteService $actualiteService
     */
    public function setActualiteService(ActualiteService $actualiteService)
    {
        $this->actualiteService = $actualiteService;
    }
}