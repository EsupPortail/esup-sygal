<?php

namespace Soutenance\Service\Evenement;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EvenementServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return EvenementService
     */
    public function __invoke(ContainerInterface $container) : EvenementService
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $service = new EvenementService();
        $service->setEntityManager($entityManager);
        return $service;
    }
}