<?php

namespace Application\Service\DomaineHal;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class DomaineHalServiceFactory {

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : DomaineHalService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new DomaineHalService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}