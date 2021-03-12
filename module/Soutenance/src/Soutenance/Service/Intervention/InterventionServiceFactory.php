<?php

namespace Soutenance\Service\Intervention;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class InterventionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return InterventionService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');

        $service = new InterventionService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        return $service;
    }
}