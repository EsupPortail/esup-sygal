<?php

namespace Formation\Service\Session;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class SessionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionService
     */
    public function __invoke(ContainerInterface $container) : SessionService
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new SessionService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}