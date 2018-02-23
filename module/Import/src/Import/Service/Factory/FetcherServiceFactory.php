<?php

namespace Import\Service\Factory;

use Import\Service\FetcherService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;
use UnicaenApp\Exception;

class FetcherServiceFactory {

    public function __invoke(ContainerInterface $container, $requestedName, $options = null) {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');

        if (!isset($config['users']['login'])) {
            throw new Exception\MandatoryValueException("La clef <strong>login</strong> est manquante dans le fichier de configuration local de l'Import");
        }
        if (!isset($config['users']['password'])) {
            throw new Exception\MandatoryValueException("La clef <strong>password</strong> est manquante dans le fichier de configuration local de l'Import");
        }

        $service = new FetcherService($entityManager, $config);
        return $service;
    }
}