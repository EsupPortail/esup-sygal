<?php

namespace Formation\Service\Seance;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class SeanceServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceService
     */
    public function __invoke(ContainerInterface $container)
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