<?php

namespace Application\Command;

use Application\Service\Fichier\FichierService;
use Zend\ServiceManager\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidationFichierCinesCommandFactory
{
    function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        if (!isset($config['sodoct']['archivabilite']['script_path'])) {
            throw new InvalidArgumentException("Option de config sodoct.archivabilite.script_path introuvable");
        }

        $scriptPath = $config['sodoct']['archivabilite']['script_path'];

        /** @var FichierService $fichierService */
        $fichierService = $serviceLocator->get(FichierService::class);

        $command = new ValidationFichierCinesCommand($scriptPath);
        $command->setFichierService($fichierService);

        return $command;
    }
}