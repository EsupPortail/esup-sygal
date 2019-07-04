<?php

namespace Application\Service\FichierThese;

trait FichierTheseServiceAwareTrait
{
    /**
     * @var FichierTheseService
     */
    protected $fichierTheseService;

    /**
     * @param FichierTheseService $fichierTheseService
     */
    public function setFichierTheseService(FichierTheseService $fichierTheseService)
    {
        $this->fichierTheseService = $fichierTheseService;
    }
}