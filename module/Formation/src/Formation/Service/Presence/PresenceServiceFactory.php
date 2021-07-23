<?php

namespace Formation\Service\Presence;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class PresenceServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return PresenceService
     */
    public function __invoke(ContainerInterface $container)
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