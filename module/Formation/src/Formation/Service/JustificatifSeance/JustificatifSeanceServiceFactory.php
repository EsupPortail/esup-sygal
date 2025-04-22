<?php

namespace Formation\Service\JustificatifSeance;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class JustificatifSeanceServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return JustificatifSeanceService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : JustificatifSeanceService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new JustificatifSeanceService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}