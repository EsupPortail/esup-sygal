<?php

namespace  Soutenance\Service\Avis;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class AvisServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $servicelocator->get('UserContextService');

        /** @var AvisService $service */
        $service = new AvisService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;
    }
}
