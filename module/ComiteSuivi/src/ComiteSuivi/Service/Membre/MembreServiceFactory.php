<?php

namespace ComiteSuivi\Service\Membre;

use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class MembreServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var SourceService $sourceService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $sourceService = $serviceLocator->get('SourceService');
        $userContextService = $serviceLocator->get('authUserContext');

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setSourceService($sourceService);
        $service->setUserContextService($userContextService);

        return $service;

    }
}