<?php

namespace RapportActivite\Service;

use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Fichier\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use Retraitement\Service\RetraitementService;

class RapportActiviteServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportActiviteService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteService
    {
        /**
         * @var FichierService $fichierService
         * @var \Fichier\Service\Fichier\FichierStorageService $fileService
         * @var VersionFichierService $versionFichierService
         * @var \Fichier\Service\ValiditeFichier\ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NatureFichierService $natureFichierService
         * @var RapportActiviteAvisService $rapportActiviteAvisService
         * @var RapportActiviteValidationService $rapportValidationService
         * @var StructureDocumentService $structureDocumentService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FichierStorageService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $etablissementService = $container->get('EtablissementService');
        $natureFichierService = $container->get('NatureFichierService');
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);
        $rapportValidationService = $container->get(RapportActiviteValidationService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);
        $pageValidationPdfExporter = $this->createPageDeCouverturePdfExporter($container);

        $service = new RapportActiviteService();

        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setEtablissementService($etablissementService);
        $service->setNatureFichierService($natureFichierService);
        $service->setRapportActiviteAvisService($rapportActiviteAvisService);
        $service->setRapportActiviteValidationService($rapportValidationService);
        $service->setStructureDocumentService($structureDocumentService);
        $service->setPageValidationPdfExporter($pageValidationPdfExporter);
        $service->setEventManager($container->get('EventManager'));

        return $service;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createPageDeCouverturePdfExporter(ContainerInterface $container): PageValidationPdfExporter
    {
        $config = $container->get('Config');

        $pdcConfig = $config['rapport-activite']['page_de_validation'] ?: $config['rapport-activite']['page_de_couverture'];
        $templateConfig = $pdcConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        /** @var PageValidationPdfExporter $pageValidationPdfExporter */
        $pageValidationPdfExporter = $container->get(PageValidationPdfExporter::class);
        $pageValidationPdfExporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $pageValidationPdfExporter;
    }
}