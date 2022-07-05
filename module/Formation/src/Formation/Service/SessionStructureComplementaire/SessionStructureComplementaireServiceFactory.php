<?php

namespace Formation\Service\SessionStructureComplementaire;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class SessionStructureComplementaireServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureComplementaireService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureComplementaireService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new SessionStructureComplementaireService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}