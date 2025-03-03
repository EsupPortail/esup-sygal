<?php

namespace Application\Service\VersionDiplome;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class VersionDiplomeServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : VersionDiplomeService
    {
        $service = new VersionDiplomeService();

        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($entityManager);

        return $service;
    }
}