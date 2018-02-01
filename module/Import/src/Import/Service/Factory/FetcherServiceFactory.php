<?php

namespace Import\Service\Factory;

use Import\Service\FetcherService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class FetcherServiceFactory {

    public function __invoke(ContainerInterface $container, $requestedName, $options = null) {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');
        $service = new FetcherService($entityManager, $config);
        return $service;
    }
}