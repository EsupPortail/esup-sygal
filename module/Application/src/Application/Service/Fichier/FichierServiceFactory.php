<?php

namespace Application\Service\Fichier;

use Application\Command\ValidationFichierCinesCommand;
use Application\Entity\Db\VersionFichier;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Validator\FichierCinesValidator;
use Retraitement\Form\Retraitement;
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
         * @var VersionFichierService $versionFichierService
         * @var ValiditeFichierService $validiteFichierService
         * @var RetraitementService $retraitementService
         */
        $versionFichierService = $serviceLocator->get('VersionFichierService');
        $validiteFichierService = $serviceLocator->get('ValiditeFichierService');
        $retraitementService = $serviceLocator->get('RetraitementService');

        $service = new FichierService();
        $service->setFichierCinesValidator($fichierCinesValidator);
        $service->setVersionFichierService($versionFichierService);
        $service->setValiditeFichierService($validiteFichierService);
        $service->setRetraitementService($retraitementService);

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