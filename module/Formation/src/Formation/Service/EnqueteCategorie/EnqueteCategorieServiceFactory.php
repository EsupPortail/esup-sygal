<?php

namespace Formation\Service\EnqueteCategorie;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EnqueteCategorieServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteCategorieService
     */
    public function __invoke(ContainerInterface $container)
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