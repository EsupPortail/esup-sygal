<?php

namespace Soutenance\Service\Membre;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;

class MembreServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var QualiteService $qualiteService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $qualiteService = $container->get(QualiteService::class);

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setQualiteService($qualiteService);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
