<?php

namespace Import\Service\Factory;

use Doctrine\ORM\EntityManager;
use Import\Service\DbService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class DbServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new DbService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}