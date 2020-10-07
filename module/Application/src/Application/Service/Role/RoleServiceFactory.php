<?php

namespace Application\Service\Role;

use Application\Service\Profil\ProfilService;
use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class RoleServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RoleService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var SourceService $sourceService
         * @var ProfilService $profilService
         */
        $sourceService = $container->get(SourceService::class);
        $profilService = $container->get(ProfilService::class);

        $service = new RoleService();
        $service->setSourceService($sourceService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setProfilService($profilService);

        return $service;
    }
}
