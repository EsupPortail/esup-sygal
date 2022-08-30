<?php

namespace RapportActivite\Service\Fichier;

use Fichier\Exporter\PageFichierIntrouvablePdfExporter;
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
        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);
        /** @var FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);
        /** @var PageValidationPdfExporter $pageSupplPdfExporter */
        $pageSupplPdfExporter = $container->get(PageValidationPdfExporter::class);
        /** @var PageFichierIntrouvablePdfExporter $pageFichierIntrouvablePdfExporter */
        $pageFichierIntrouvablePdfExporter = $container->get(PageFichierIntrouvablePdfExporter::class);

        $service = new RapportActiviteFichierService();
        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setPageValidationPdfExporter($pageSupplPdfExporter);
        $service->setPageFichierIntrouvablePdfExporter($pageFichierIntrouvablePdfExporter);

        return $service;
    }
}