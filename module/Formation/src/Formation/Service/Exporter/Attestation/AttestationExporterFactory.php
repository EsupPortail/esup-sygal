<?php

namespace Formation\Service\Exporter\Attestation;

use Fichier\Service\Fichier\FichierStorageService;
use Formation\Service\Url\UrlService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;
use UnicaenRenderer\Service\Rendu\RenduService;

class AttestationExporterFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : AttestationExporter
    {
        /**
         * @var FichierStorageService $fichierStorageService
         * @var RenduService $renduService
         * @var StructureService $structureService
         * @var UrlService $urlService
         */
        $fichierStorageService = $container->get(FichierStorageService::class);
        $renduService = $container->get(RenduService::class);
        $structureService = $container->get(StructureService::class);
        $urlService = $container->get(UrlService::class);
        $renderer = $container->get('ViewRenderer');

        $exporter = new AttestationExporter($renderer, 'A4');
        $exporter->setFichierStorageService($fichierStorageService);
        $exporter->setRenduService($renduService);
        $exporter->setStructureService($structureService);
        $exporter->setUrlService($urlService);
        return $exporter;
    }
}