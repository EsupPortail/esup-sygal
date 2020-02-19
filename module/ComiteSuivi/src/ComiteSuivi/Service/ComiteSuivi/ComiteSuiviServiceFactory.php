<?php

namespace ComiteSuivi\Service\ComiteSuivi;

use Application\Service\UserContextService;
use ComiteSuivi\Entity\Db\ComiteSuivi;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ComiteSuiviServiceFactory {

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ComiteSuiviService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userContextService = $serviceLocator->get('authUserContext');

        /** @var ComiteSuiviService $service */
        $service = new ComiteSuiviService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        return $service;
    }
}