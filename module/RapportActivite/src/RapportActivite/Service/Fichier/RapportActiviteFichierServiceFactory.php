<?php

namespace RapportActivite\Service\Fichier;

use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter;

class RapportActiviteFichierServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteFichierService
    {
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FichierStorageService::class);
        $pageSupplPdfExporter = $container->get(PageValidationPdfExporter::class);

        $service = new RapportActiviteFichierService();
        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setPageValidationPdfExporter($pageSupplPdfExporter);

        return $service;
    }
}