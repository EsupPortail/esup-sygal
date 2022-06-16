<?php

namespace Fichier\Service\Fichier;

use Fichier\Service\File\FileService;
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
         * @var FileService $fileService
         * @var VersionFichierService $versionFichierService
         * @var NatureFichierService $natureFichierService
         */
        $fileService = $container->get(FileService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $natureFichierService = $container->get('NatureFichierService');

        $service = new FichierService();

        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setNatureFichierService($natureFichierService);

        return $service;
    }
}