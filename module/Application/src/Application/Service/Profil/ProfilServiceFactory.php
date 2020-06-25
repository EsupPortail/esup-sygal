<?php

namespace Application\Service\Profil;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\PrivilegeService;

class ProfilServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var PrivilegeService $privilegeService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $privilegeService = $container->get('UnicaenAuth\Service\Privilege');

        /** @var ProfilService $service */
        $service = new ProfilService();
        $service->setEntityManager($entityManager);
        $service->setServicePrivilege($privilegeService);

        return $service;
    }
}