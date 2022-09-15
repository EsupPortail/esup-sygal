<?php

namespace Application\Service\Discipline;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class DisciplineServiceFactory {

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : DisciplineService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new DisciplineService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}