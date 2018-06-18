<?php

namespace Application\Command;

use Zend\ServiceManager\Exception\InvalidArgumentException;
use Zend\ServiceManager\ServiceLocatorInterface;

class CheckWSValidationFichierCinesCommandFactory
{
    function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        if (!isset($config['sygal']['archivabilite']['check_ws_script_path'])) {
            throw new InvalidArgumentException("Option de config sygal.archivabilite.check_ws_script_path introuvable");
        }

        $scriptPath = $config['sygal']['archivabilite']['check_ws_script_path'];

        return new CheckWSValidationFichierCinesCommand(new ShellScriptRunner($scriptPath));
    }
}