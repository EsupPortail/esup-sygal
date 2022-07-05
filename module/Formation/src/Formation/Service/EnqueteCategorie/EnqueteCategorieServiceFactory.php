<?php

namespace Formation\Service\EnqueteCategorie;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EnqueteCategorieServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteCategorieService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : EnqueteCategorieService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new EnqueteCategorieService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}