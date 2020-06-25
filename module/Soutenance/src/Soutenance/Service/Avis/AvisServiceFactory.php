<?php

namespace Soutenance\Service\Avis;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class AvisServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return AvisService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');

        /** @var AvisService $service */
        $service = new AvisService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
