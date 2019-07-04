<?php

namespace Application\Service\Fichier;

use Application\Service\File\FileService;
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
         */
        $fileService = $serviceLocator->get(FileService::class);
        $versionFichierService = $serviceLocator->get('VersionFichierService');

        $service = new FichierService();

        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);

        return $service;
    }
}