<?php

namespace ComiteSuivi\Service\CompteRendu;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class CompteRenduServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $serviceLocator->get('authUserContext');

        /** @var CompteRenduService $service */
        $service = new CompteRenduService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;

    }
}