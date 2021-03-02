<?php

namespace Retraitement\Service;

use Application\Command\CommandInterface;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\InvalidArgumentException;

class RetraitementServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RetraitementService
     */
    public function __invoke(ContainerInterface $container)
    {
        $command = $this->createCommand($container);

        return new RetraitementService($command);
    }

    /**
     * @param ContainerInterface $container
     * @return CommandInterface
     */
    private function createCommand(ContainerInterface $container)
    {
        $config = $container->get('config');

        if (!isset($config['sygal']['retraitement']['command']['class'])) {
            throw new InvalidArgumentException("Option de config 'sygal.retraitement.command.class' introuvable");
        }
        $commandClass = $config['sygal']['retraitement']['command']['class'];
        if (!class_exists($commandClass)) {
            throw new InvalidArgumentException("La classe spécifiée dans l'option de 'config sygal.retraitement.command.class' n'existe pas");
        }

        /** @var CommandInterface $command */
        $command = new $commandClass;

        if (isset($config['sygal']['retraitement']['command']['options'])) {
            $command->setOptions($config['sygal']['retraitement']['command']['options']);
        }

        return $command;
    }
}