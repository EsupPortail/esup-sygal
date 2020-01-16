<?php

namespace Retraitement\Service;

use Application\Command\CommandInterface;
use Zend\ServiceManager\Exception\InvalidArgumentException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RetraitementServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RetraitementService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $command = $this->createCommand($serviceLocator);

        return new RetraitementService($command);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return CommandInterface
     */
    private function createCommand(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

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