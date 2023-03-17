<?php

namespace Soutenance\Service\Qualite;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

class QualiteServiceFactory
{

    /**
     * @param ContainerInterface $container
     * @return QualiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get(\UnicaenAuthentification\Service\UserContext::class);

        /** @var QualiteService $service */
        $service = new QualiteService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        return $service;
    }
}