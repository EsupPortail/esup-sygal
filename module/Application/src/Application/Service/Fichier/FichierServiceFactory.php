<?php

namespace Application\Service\Fichier;

use Application\Service\File\FileService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\VersionFichier\VersionFichierService;
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
         * @var UtilisateurService $utilisateurService
         */
        $fileService = $container->get(FileService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $natureFichierService = $container->get('NatureFichierService');
        $utilisateurService = $container->get(UtilisateurService::class);

        $service = new FichierService();

        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setNatureFichierService($natureFichierService);
        $service->setUtilisateurService($utilisateurService);

        return $service;
    }
}