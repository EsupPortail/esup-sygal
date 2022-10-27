<?php

namespace These\Service\FichierThese;

use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Application\Service\Notification\NotifierService;
use These\Service\PageDeCouverture\PageDeCouverturePdfExporter;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Fichier\Validator\FichierCinesValidator;
use Interop\Container\ContainerInterface;
use Retraitement\Service\RetraitementService;

class FichierTheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return \These\Service\FichierThese\FichierTheseService
     */
    public function __invoke(ContainerInterface $container): FichierTheseService
    {
        $fichierCinesValidator = $this->createFichierCinesValidator($container);

        /**
         * @var FichierService $fichierService
         * @var FichierStorageService $fileService
         * @var VersionFichierService $versionFichierService
         * @var ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NotifierService $notifierService
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FichierStorageService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $validiteFichierService = $container->get('ValiditeFichierService');
        $retraitementService = $container->get('RetraitementService');
        $etablissementService = $container->get('EtablissementService');
        $notifierService = $container->get(NotifierService::class);
        $pdcPdfExporter = $this->createPageDeCouverturePdfExporter($container);

        $service = new FichierTheseService();

        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fileService);
        $service->setFichierCinesValidator($fichierCinesValidator);
        $service->setVersionFichierService($versionFichierService);
        $service->setValiditeFichierService($validiteFichierService);
        $service->setRetraitementService($retraitementService);
        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);
        $service->setPageDeCouverturePdfExporter($pdcPdfExporter);

        return $service;
    }

    private function createPageDeCouverturePdfExporter(ContainerInterface $container): PageDeCouverturePdfExporter
    {
        $config = $container->get('Config');

        $pdcConfig = $config['sygal']['page_de_couverture'];
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

    private function createFichierCinesValidator(ContainerInterface $container): FichierCinesValidator
    {
        /** @var \Fichier\Command\TestArchivabiliteShellCommand $command */
        $command = $container->get('ValidationFichierCinesCommand');

        $validator = new FichierCinesValidator();
        $validator->setShellCommand($command);

        return $validator;
    }
}