<?php

namespace  Soutenance\Service\Membre;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Soutenance\Service\Qualite\QualiteService;
use Zend\ServiceManager\ServiceLocatorInterface;

class MembreServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var QualiteService $qualiteService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $servicelocator->get('UserContextService');
        $qualiteService = $servicelocator->get(QualiteService::class);

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setQualiteService($qualiteService);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
