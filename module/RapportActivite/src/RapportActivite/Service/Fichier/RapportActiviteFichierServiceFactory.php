<?php

namespace RapportActivite\Service\Fichier;

use Application\Service\Fichier\FichierService;
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
        $pageSupplPdfExporter = $container->get(PageValidationPdfExporter::class);

        $service = new RapportActiviteFichierService();
        $service->setFichierService($fichierService);
        $service->setPageValidationPdfExporter($pageSupplPdfExporter);

        return $service;
    }
}