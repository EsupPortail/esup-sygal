<?php

namespace Depot\Service\FichierHDR;

trait FichierHDRServiceAwareTrait
{
    /**
     * @var FichierHDRService
     */
    protected $fichierHDRService;

    /**
     * @param FichierHDRService $fichierHDRService
     */
    public function setFichierHDRService(FichierHDRService $fichierHDRService)
    {
        $this->fichierHDRService = $fichierHDRService;
    }
}