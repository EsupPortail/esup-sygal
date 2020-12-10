<?php

namespace Application\Service\CoEncadrant;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class CoEncadrantServiceFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new CoEncadrantService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}