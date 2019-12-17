<?php

namespace Soutenance\Service\QualiteLibelleSupplementaire;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class QualiteLibelleSupplementaireServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContext
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userContext = $serviceLocator->get('UserContextService');

        /**
         * @var QualiteLibelleSupplementaireService $service
         */
        $service = new QualiteLibelleSupplementaireService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContext);
        return $service;

    }
}