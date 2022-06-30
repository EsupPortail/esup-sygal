<?php

namespace Formation\Service\Presence;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PresenceServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return PresenceService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : PresenceService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new PresenceService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}