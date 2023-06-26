<?php

namespace Doctorant\Service\MissionEnseignement;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MissionEnseignementServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return MissionEnseignementService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : MissionEnseignementService
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new MissionEnseignementService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}