<?php

namespace Formation\Service\SessionStructureValide;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionStructureValideServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureValideService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureValideService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new SessionStructureValideService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}