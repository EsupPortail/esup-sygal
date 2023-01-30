<?php

namespace RapportActivite\Service;

use Application\Service\Validation\ValidationService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Avis\RapportActiviteAvisRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Fichier\Exporter\PageValidationPdfExporter;
use RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporter;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use Retraitement\Service\RetraitementService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;

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

        /** @var \RapportActivite\Rule\Avis\RapportActiviteAvisRule $rapportActiviteAvisRule */
        $rapportActiviteAvisRule = $container->get(RapportActiviteAvisRule::class);
        $service->setRapportActiviteAvisRule($rapportActiviteAvisRule);

        /** @var \RapportActivite\Service\Fichier\Exporter\RapportActivitePdfExporter $rapportActivitePdfExporter */
        $rapportActivitePdfExporter = $container->get(RapportActivitePdfExporter::class);
        $service->setRapportActivitePdfExporter($rapportActivitePdfExporter);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $service->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        /** @var \Structure\Service\Structure\StructureService $structureService */
        $structureService = $container->get(StructureService::class);
        $service->setStructureService($structureService);

        /** @var \Application\Service\Validation\ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $service->setValidationService($validationService);

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