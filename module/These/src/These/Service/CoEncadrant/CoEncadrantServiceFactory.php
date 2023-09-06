<?php

namespace These\Service\CoEncadrant;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CoEncadrantServiceFactory
{

    /**
     * @param ContainerInterface $container
     * @return CoEncadrantService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): CoEncadrantService
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