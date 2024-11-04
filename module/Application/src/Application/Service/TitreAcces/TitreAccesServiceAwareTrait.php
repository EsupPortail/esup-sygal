<?php

namespace Application\Service\TitreAcces;

trait TitreAccesServiceAwareTrait
{
    /**
     * @var TitreAccesService
     */
    protected TitreAccesService $titreAccesService;

    /**
     * @param TitreAccesService $titreAccesService
     */
    public function setTitreAccesService(TitreAccesService $titreAccesService)
    {
        $this->titreAccesService = $titreAccesService;
    }
}