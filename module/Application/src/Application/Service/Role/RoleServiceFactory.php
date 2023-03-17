<?php

namespace Application\Service\Role;

use Application\Service\Profil\ProfilService;
use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Psr\Container\ContainerInterface;

class RoleServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RoleService
    {
        /**
         * @var SourceService $sourceService
         * @var ProfilService $profilService
         */
        $sourceService = $container->get(SourceService::class);
        $profilService = $container->get(ProfilService::class);

        $service = new RoleService();
        $service->setSourceService($sourceService);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setProfilService($profilService);

        return $service;
    }
}
