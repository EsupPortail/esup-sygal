<?php

namespace Fichier\Service\Fichier;

trait FichierStorageServiceAwareTrait
{
    /**
     * @var FichierStorageService
     */
    protected FichierStorageService $fichierStorageService;

    /**
     * @param FichierStorageService $fichierStorageService
     */
    public function setFichierStorageService(FichierStorageService $fichierStorageService)
    {
        $this->fichierStorageService = $fichierStorageService;
    }
}