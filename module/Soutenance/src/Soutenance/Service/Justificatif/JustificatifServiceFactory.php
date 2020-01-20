<?php

namespace Soutenance\Service\Justificatif;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class JustificatifServiceFactory {

    public function __invoke(ServiceLocatorInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $useContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');

        /** @var JustificatifService $service */
        $service = new JustificatifService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        return $service;
    }
}