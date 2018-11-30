<?php

namespace Application\Service\Fichier;

use Application\Command\ValidationFichierCinesCommand;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Validator\FichierCinesValidator;
use Retraitement\Service\RetraitementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FichierServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $fichierCinesValidator = $this->createFichierCinesValidator($serviceLocator);

        /**
         * @var FileService $fileService
         * @var VersionFichierService $versionFichierService
         * @var ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         * @var EtablissementService $etablissementService
         * @var NotifierService $notifierService
         * @var \Zend\View\Renderer\PhpRenderer $renderer
         */
        $fileService = $serviceLocator->get('FileService');
        $versionFichierService = $serviceLocator->get('VersionFichierService');
        $validiteFichierService = $serviceLocator->get('ValiditeFichierService');
        $retraitementService = $serviceLocator->get('RetraitementService');
        $etablissementService = $serviceLocator->get('EtablissementService');
        $notifierService = $serviceLocator->get(NotifierService::class);
        $renderer = $serviceLocator->get('view_renderer');

        $service = new FichierService();

        $service->setFileService($fileService);
        $service->setFichierCinesValidator($fichierCinesValidator);
        $service->setVersionFichierService($versionFichierService);
        $service->setValiditeFichierService($validiteFichierService);
        $service->setRetraitementService($retraitementService);
        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);
        $service->setRenderer($renderer);

        return $service;
    }

    private function createFichierCinesValidator(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ValidationFichierCinesCommand $command */
        $command = $serviceLocator->get('ValidationFichierCinesCommand');

        $validator = new FichierCinesValidator();
        $validator->setCommand($command);

        return $validator;
    }

}