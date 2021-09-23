<?php

namespace Application\Service\FichierThese;

use Application\Command\ValidationFichierCinesCommand;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\PageDeCouverture\PageDeCouverturePdfExporter;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Validator\FichierCinesValidator;
use Interop\Container\ContainerInterface;
use Retraitement\Service\RetraitementService;

class FichierTheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return \Application\Service\FichierThese\FichierTheseService
     */
    public function __invoke(ContainerInterface $container): FichierTheseService
    {
        $fichierCinesValidator = $this->createFichierCinesValidator($container);

        /**
         * @var FichierService $fichierService
         * @var FileService $fileService
         * @var VersionFichierService $versionFichierService
         * @var ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NotifierService $notifierService
         * @var PageDeCouverturePdfExporter $pdcPdfExporter
         */
        $fichierService = $container->get(FichierService::class);
        $fileService = $container->get(FileService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $validiteFichierService = $container->get('ValiditeFichierService');
        $retraitementService = $container->get('RetraitementService');
        $etablissementService = $container->get('EtablissementService');
        $notifierService = $container->get(NotifierService::class);
        $pdcPdfExporter = $container->get(PageDeCouverturePdfExporter::class);

        $service = new FichierTheseService();

        $service->setFichierService($fichierService);
        $service->setFileService($fileService);
        $service->setFichierCinesValidator($fichierCinesValidator);
        $service->setVersionFichierService($versionFichierService);
        $service->setValiditeFichierService($validiteFichierService);
        $service->setRetraitementService($retraitementService);
        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);
        $service->setPageDeCouverturePdfExporter($pdcPdfExporter);

        return $service;
    }

    private function createFichierCinesValidator(ContainerInterface $container)
    {
        /** @var ValidationFichierCinesCommand $command */
        $command = $container->get('ValidationFichierCinesCommand');

        $validator = new FichierCinesValidator();
        $validator->setCommand($command);

        return $validator;
    }

}