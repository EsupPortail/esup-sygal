<?php

namespace Application\Service\TitreAcces;

trait TitreAccesServiceAwareTrait
{
    /**
     * @var TitreAccesService
     */
    protected TitreAccesService $TitreAccesService;

    /**
     * @param TitreAccesService $TitreAccesService
     */
    public function setTitreAccesService(TitreAccesService $TitreAccesService)
    {
        $this->TitreAccesService = $TitreAccesService;
    }
}