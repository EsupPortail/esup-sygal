<?php

namespace ComiteSuivi\Service\Membre;

use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class MembreServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return MembreService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var SourceService $sourceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $sourceService = $container->get('SourceService');
        $userContextService = $container->get('authUserContext');

        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setSourceService($sourceService);
        $service->setUserContextService($userContextService);

        return $service;

    }
}