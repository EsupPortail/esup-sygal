<?php

namespace ComiteSuivi\Service\ComiteSuivi;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class ComiteSuiviServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return ComiteSuiviService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('authUserContext');

        $service = new ComiteSuiviService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;
    }
}