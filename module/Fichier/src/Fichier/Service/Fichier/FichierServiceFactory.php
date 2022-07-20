<?php

namespace Fichier\Service\Fichier;

use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;

class FichierServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FichierService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var FichierStorageService $fileService
         * @var VersionFichierService $versionFichierService
         * @var NatureFichierService $natureFichierService
         */
        $fileService = $container->get(FichierStorageService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $natureFichierService = $container->get('NatureFichierService');

        $service = new FichierService();

        $service->setFichierStorageService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setNatureFichierService($natureFichierService);

        return $service;
    }
}