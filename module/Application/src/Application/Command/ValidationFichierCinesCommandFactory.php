<?php

namespace Application\Command;

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

        return new ValidationFichierCinesCommand($scriptPath);
    }
}