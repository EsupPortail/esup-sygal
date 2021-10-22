<?php

namespace Application\Command;

use Interop\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class TestArchivabiliteShellCommandFactory
{
    public function __invoke(ContainerInterface $container): TestArchivabiliteShellCommand
    {
        $config = $container->get('config');

        Assert::keyExists($config, 'sygal', "La clé %s est introuvable dans la config de l'application");
        Assert::keyExists($config['sygal'], 'archivabilite', "La clé %s est introuvable dans la config 'sygal'");
        $options = $config['sygal']['archivabilite'];

        Assert::keyExists($options, 'script_path', "La clé %s est introuvable dans la config 'archivabilite'");
        $scriptPath = $config['sygal']['archivabilite']['script_path'];

        return new TestArchivabiliteShellCommand($scriptPath, $options);
    }
}