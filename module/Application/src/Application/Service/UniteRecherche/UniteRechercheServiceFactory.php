<?php

namespace Application\Service\UniteRecherche;

use Application\Service\Role\RoleService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class UniteRechercheServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return UniteRechercheService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var RoleService $roleService */
        $roleService = $container->get('RoleService');

        $service = new UniteRechercheService();
        $service->setRoleService($roleService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
