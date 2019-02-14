<?php

namespace Application\Service\EcoleDoctorale;

use Application\Service\Role\RoleService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class EcoleDoctoraleServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return EcoleDoctoraleService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RoleService $roleService */
        $roleService = $serviceLocator->get('RoleService');

        $service = new EcoleDoctoraleService();
        $service->setRoleService($roleService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
