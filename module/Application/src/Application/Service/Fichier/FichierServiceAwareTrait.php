<?php

namespace Application\Service\Fichier;

trait FichierServiceAwareTrait
{
    /**
     * @var FichierService
     */
    protected $fichierService;

    /**
     * @param FichierService $fichierService
     */
    public function setFichierService(FichierService $fichierService)
    {
        $this->fichierService = $fichierService;
    }
}