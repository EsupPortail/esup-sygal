<?php

namespace Application\Service\VersionFichier;

trait VersionFichierServiceAwareTrait
{
    /**
     * @var VersionFichierService
     */
    protected $versionFichierService;

    /**
     * @param VersionFichierService $versionFichierService
     */
    public function setVersionFichierService(VersionFichierService $versionFichierService)
    {
        $this->versionFichierService = $versionFichierService;
    }
}