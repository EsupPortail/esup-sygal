<?php

namespace Depot\Service\FichierHDR;

use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class FichierHDRServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return \Depot\Service\FichierThese\FichierTheseService
     */
    public function __invoke(ContainerInterface $container): FichierHDRService
    {
        /**
         * @var FichierService $fichierService
         * @var FichierStorageService $fileService
         * @var VersionFichierService $versionFichierService
         * @var \Fichier\Service\ValiditeFichier\ValiditeFichierService $validiteFichierService
         * @var EtablissementService $etablissementService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FichierStorageService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $validiteFichierService = $container->get('ValiditeFichierService');
        $etablissementService = $container->get('EtablissementService');

        $service = new FichierHDRService();

        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setValiditeFichierService($validiteFichierService);
        $service->setEtablissementService($etablissementService);

        return $service;
    }
}