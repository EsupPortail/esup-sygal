<?php

namespace Admission\Service\Exporter\Recapitulatif;

use Admission\Service\Admission\AdmissionService;
use Fichier\Service\Fichier\FichierStorageService;
use Admission\Service\Url\UrlService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use UnicaenRenderer\Service\Rendu\RenduService;

class RecapitulatifExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : RecapitulatifExporter
    {
        /**
         * @var EtablissementService $etablissementService
         * @var FichierStorageService $fichierStorageService
         * @var RenduService $renduService
         * @var StructureService $structureService
         * @var UrlService $urlService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $renduService = $container->get(RenduService::class);
        $structureService = $container->get(StructureService::class);
        $urlService = $container->get(UrlService::class);
        $admissionService = $container->get(AdmissionService::class);
        $renderer = $container->get('ViewRenderer');

        $exporter = new RecapitulatifExporter($renderer, 'A4');
        $exporter->setEtablissementService($etablissementService);
        $exporter->setFichierStorageService($fichierStorageService);
        $exporter->setRenduService($renduService);
        $exporter->setStructureService($structureService);
        $exporter->setUrlService($urlService);
        $exporter->setAdmissionService($admissionService);
        return $exporter;
    }
}