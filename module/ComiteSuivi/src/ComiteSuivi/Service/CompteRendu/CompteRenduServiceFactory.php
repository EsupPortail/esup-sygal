<?php

namespace ComiteSuivi\Service\CompteRendu;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class CompteRenduServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return CompteRenduService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('authUserContext');

        $service = new CompteRenduService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;

    }
}