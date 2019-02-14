<?php

namespace Application\Service\UniteRecherche;

use Application\Service\Role\RoleService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class UniteRechercheServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return UniteRechercheService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RoleService $roleService */
        $roleService = $serviceLocator->get('RoleService');

        $service = new UniteRechercheService();
        $service->setRoleService($roleService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
