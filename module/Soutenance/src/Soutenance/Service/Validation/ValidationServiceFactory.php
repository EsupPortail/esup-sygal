<?php

namespace Soutenance\Service\Validation;


use Application\Service\Individu\IndividuService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidationServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var IndividuService $individuService
         */
        $entityManager      = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $serviceLocator->get('UserContextService');
        $individuService    = $serviceLocator->get('IndividuService');

        /** @var ValidationService $service */
        $service = new ValidationService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setIndividuService($individuService);
        return $service;
    }
}