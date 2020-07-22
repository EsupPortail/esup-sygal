<?php

namespace Application\Service\RapportAnnuel;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\Notification\NotifierService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Retraitement\Service\RetraitementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class RapportAnnuelServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportAnnuelService
     */
    public function createService(ContainerInterface $container)
    {
        /**
         * @var FichierService $fichierService
         * @var FileService $fileService
         * @var VersionFichierService $versionFichierService
         * @var ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NotifierService $notifierService
         * @var NatureFichierService $natureFichierService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FileService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $etablissementService = $container->get('EtablissementService');
        $notifierService = $container->get(NotifierService::class);
        $natureFichierService = $container->get('NatureFichierService');

        $service = new RapportAnnuelService();

        $service->setFichierService($fichierService);
        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);
        $service->setNatureFichierService($natureFichierService);

        return $service;
    }
}