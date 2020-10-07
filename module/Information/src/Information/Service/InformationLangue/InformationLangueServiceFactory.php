<?php

namespace Information\Service\InformationLangue;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class InformationLangueServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return InformationLangueService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new InformationLangueService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}