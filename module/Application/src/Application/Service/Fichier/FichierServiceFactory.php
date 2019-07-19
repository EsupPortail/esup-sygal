<?php

namespace Application\Service\Fichier;

use Application\Service\File\FileService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FichierServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return FichierService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var FileService $fileService
         * @var VersionFichierService $versionFichierService
         * @var NatureFichierService $natureFichierService
         */
        $fileService = $serviceLocator->get(FileService::class);
        $versionFichierService = $serviceLocator->get('VersionFichierService');
        $natureFichierService = $serviceLocator->get('NatureFichierService');

        $service = new FichierService();

        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setNatureFichierService($natureFichierService);

        return $service;
    }
}