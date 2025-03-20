<?php

namespace Fichier\Command;

use Interop\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class TestArchivabiliteShellCommandFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TestArchivabiliteShellCommand
    {
        $config = $container->get('config');

        Assert::keyExists($config, 'sygal', "La clé %s est introuvable dans la config de l'application");
        Assert::keyExists($config['sygal'], 'archivabilite', "La clé %s est introuvable dans la config 'sygal'");
        $options = $config['sygal']['archivabilite'];

        Assert::keyExists($options, 'script_path', "La clé %s est introuvable dans la config 'archivabilite'");
        $scriptPath = $config['sygal']['archivabilite']['script_path'];

        $wsUrl =  $options['ws_url'] ?? null;

        $command = new TestArchivabiliteShellCommand($scriptPath, $options);

        if ($wsUrl !== null) {
            $command->setUrl($wsUrl);
        }

        return $command;
    }
}