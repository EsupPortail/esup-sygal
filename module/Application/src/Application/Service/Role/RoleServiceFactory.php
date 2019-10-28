<?php

namespace Application\Service\Role;

use Application\Service\Profil\ProfilService;
use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class RoleServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RoleService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var SourceService $sourceService
         * @var ProfilService $profilService
         */
        $sourceService = $serviceLocator->get(SourceService::class);
        $profilService = $serviceLocator->get(ProfilService::class);


        $service = new RoleService();
        $service->setSourceService($sourceService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setProfilService($profilService);

        return $service;
    }
}
