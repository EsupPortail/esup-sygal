<?php

namespace Application\Service\Fichier;

use Application\Command\ValidationFichierCinesCommand;
use Application\Service\ValiditeFichier\ValiditeFichierService;
use Application\Service\VersionFichier\VersionFichierService;
use Application\Validator\FichierCinesValidator;
use Retraitement\Service\RetraitementService;
use UnicaenApp\Exception\RuntimeException;
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

        $service->setRootDirectoryPath($this->getRootDirectoryPath($serviceLocator));

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

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return string
     */
    private function getRootDirectoryPath(ServiceLocatorInterface $serviceLocator)
    {
        /** @var array $config */
        $config = $serviceLocator->get('config');

        if (empty($config['fichier']['root_dir_path'])) {
            throw new RuntimeException(
                "Vous devez spécifier dans la config le chemin du répertoire de destination des fichiers (clé fichier.root_dir_path).");
        }

        $path = $config['fichier']['root_dir_path'];

        if (! is_readable($path)) {
            throw new RuntimeException(
                "Le chemin du répertoire de destination des fichiers doit exister et être accessible : " . $path);
        }

        return $path;
    }
}