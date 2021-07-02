<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Service\Presence\PresenceService;
use Interop\Container\ContainerInterface;

class PresenceControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return PresenceController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var PresenceService $presenceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $presenceService = $container->get(PresenceService::class);

        $controller = new PresenceController();
        $controller->setEntityManager($entityManager);
        $controller->setPresenceService($presenceService);
        return $controller;
    }
}