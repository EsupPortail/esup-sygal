<?php

namespace Soutenance\Service\Adresse;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdresseServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return AdresseService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdresseService
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new AdresseService();
        $service->setObjectManager($entityManager);
        return $service;
    }
}