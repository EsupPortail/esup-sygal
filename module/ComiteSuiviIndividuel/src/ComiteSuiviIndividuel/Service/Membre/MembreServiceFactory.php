<?php

namespace ComiteSuiviIndividuel\Service\Membre;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MembreServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return MembreService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : MembreService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new MembreService();
        $service->setEntityManager($entityManager);
        return $service;
    }

}