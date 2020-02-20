<?php

namespace ComiteSuivi\Service\Membre;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class MembreServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $serviceLocator->get('authUserContext');

        /** @var MembreService $service */
        $service = new MembreService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;

    }
}