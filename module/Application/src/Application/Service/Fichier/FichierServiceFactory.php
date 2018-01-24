<?php

namespace Application\Service\Fichier;

use Application\Command\ValidationFichierCinesCommand;
use Application\Validator\FichierCinesValidator;
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

        $service = new FichierService();
        $service->setFichierCinesValidator($fichierCinesValidator);

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
}