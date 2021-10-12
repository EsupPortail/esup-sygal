<?php

namespace Application\Command;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;

class CheckWSValidationFichierCinesCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['sygal']['archivabilite']['check_ws_script_path'])) {
            throw new InvalidArgumentException("Option de config sygal.archivabilite.check_ws_script_path introuvable");
        }

        $scriptPath = $config['sygal']['archivabilite']['check_ws_script_path'];

        return new CheckWSValidationFichierCinesCommand(new ShellScriptRunner($scriptPath));
    }
}