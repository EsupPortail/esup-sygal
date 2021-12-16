<?php

namespace Application\Service\Rapport;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\Notification\NotifierService;
use Application\Service\PageDeCouverture\PageDeCouverturePdfExporter;
use Application\Service\RapportValidation\RapportValidationService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Retraitement\Service\RetraitementService;
use Zend\ServiceManager\Factory\FactoryInterface;

class RapportServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RapportService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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
         * @var RapportValidationService $rapportValidationService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FileService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $etablissementService = $container->get('EtablissementService');
        $notifierService = $container->get(NotifierService::class);
        $natureFichierService = $container->get('NatureFichierService');
        $rapportValidationService = $container->get(RapportValidationService::class);
        $pdcPdfExporter = $this->createPageDeCouverturePdfExporter($container);

        $service = new RapportService();

        $service->setFichierService($fichierService);
        $service->setFileService($fileService);
        $service->setVersionFichierService($versionFichierService);
        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);
        $service->setNatureFichierService($natureFichierService);
        $service->setRapportValidationService($rapportValidationService);
        $service->setPageDeCouverturePdfExporter($pdcPdfExporter);

        return $service;
    }

    private function createPageDeCouverturePdfExporter(ContainerInterface $container): PageDeCouverturePdfExporter
    {
        $config = $container->get('Config');

        $pdcConfig = $config['sygal']['rapport']['page_de_couverture'];
        $templateConfig = $pdcConfig['template'];
        $templateFilePath = $templateConfig['phtml_file_path'];
        $cssFilePath = $templateConfig['css_file_path'];

        /** @var PageDeCouverturePdfExporter $pdcPdfExporter */
        $pdcPdfExporter = $container->get(PageDeCouverturePdfExporter::class);
        $pdcPdfExporter
            ->setTemplateFilePath($templateFilePath)
            ->setCssFilePath($cssFilePath);

        return $pdcPdfExporter;
    }
}