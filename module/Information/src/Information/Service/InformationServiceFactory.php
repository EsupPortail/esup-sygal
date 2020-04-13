<?php

namespace Information\Service;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class InformationServiceFactory {
    /**
     * @param ContainerInterface $container
     * @return InformationService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UnicaenAuth\Service\UserContext');

        $service = new InformationService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
