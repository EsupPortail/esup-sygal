<?php

namespace Structure\Service\EcoleDoctorale;

use Application\Service\Role\RoleService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class EcoleDoctoraleServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EcoleDoctoraleService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var RoleService $roleService */
        $roleService = $container->get('RoleService');

        $service = new EcoleDoctoraleService();
        $service->setRoleService($roleService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
