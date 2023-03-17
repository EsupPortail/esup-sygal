<?php

namespace Application\Service\Profil;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use UnicaenPrivilege\Service\Privilege\PrivilegeService;

class ProfilServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var PrivilegeService $privilegeService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $privilegeService = $container->get(\UnicaenPrivilege\Service\Privilege\PrivilegeService::class);

        /** @var ProfilService $service */
        $service = new ProfilService();
        $service->setEntityManager($entityManager);
        $service->setPrivilegeService($privilegeService);

        return $service;
    }
}