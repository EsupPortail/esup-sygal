<?php

namespace Import\Service\Factory;

use Doctrine\ORM\EntityManager;
use Import\Service\SynchroService;
use Interop\Container\ContainerInterface;

class SynchroServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new SynchroService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}