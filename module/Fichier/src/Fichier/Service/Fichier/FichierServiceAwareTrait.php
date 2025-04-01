<?php

namespace Fichier\Service\Fichier;

trait FichierServiceAwareTrait
{
    protected FichierService $fichierService;

    public function setFichierService(FichierService $fichierService): void
    {
        $this->fichierService = $fichierService;
    }
}