<?php

namespace Application\Service\AutorisationInscription;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\RapportValidation\RapportValidationService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Retraitement\Service\RetraitementService;
use Structure\Service\Etablissement\EtablissementService;

class AutorisationInscriptionServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return AutorisationInscriptionService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var FichierService $fichierService
         * @var \Fichier\Service\Fichier\FichierStorageService $fileService
         * @var VersionFichierService $versionFichierService
         * @var \Fichier\Service\ValiditeFichier\ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NatureFichierService $natureFichierService
         * @var RapportValidationService $rapportValidationService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FichierStorageService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $etablissementService = $container->get('EtablissementService');
        $natureFichierService = $container->get('NatureFichierService');
        $rapportValidationService = $container->get(RapportValidationService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);

        $service = new AutorisationInscriptionService();

        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setEtablissementService($etablissementService);
        $service->setNatureFichierService($natureFichierService);
        $service->setRapportValidationService($rapportValidationService);
        $service->setAnneeUnivService($anneeUnivService);

        return $service;
    }
}