<?php

namespace Formation\Service\Seance;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SeanceServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SeanceService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new SeanceService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}