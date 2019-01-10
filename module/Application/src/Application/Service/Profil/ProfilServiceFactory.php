<?php

namespace Application\Service\Profil;

use Doctrine\ORM\EntityManager;
use UnicaenAuth\Service\PrivilegeService;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProfilServiceFactory {

    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var PrivilegeService $privilegeService
         */
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $privilegeService = $serviceLocator->get('UnicaenAuth\Service\Privilege');

        /** @var ProfilService $service */
        $service = new ProfilService();
        $service->setEntityManager($entityManager);
        $service->setServicePrivilege($privilegeService);
        return $service;
    }
}