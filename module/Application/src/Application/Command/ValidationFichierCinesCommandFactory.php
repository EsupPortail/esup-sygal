<?php

namespace Application\Command;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\InvalidArgumentException;

class ValidationFichierCinesCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (!isset($config['sygal']['archivabilite']['script_path'])) {
            throw new InvalidArgumentException("Option de config sygal.archivabilite.script_path introuvable");
        }

        $scriptPath = $config['sygal']['archivabilite']['script_path'];

        return new ValidationFichierCinesCommand($scriptPath);
    }
}