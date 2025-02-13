<?php

namespace Acteur\Service\ActeurHDR;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class ActeurHDRServiceFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RoleService $roleService
         * @var SourceService $sourceService
         * @var UserContextService $userContextService;
         */
        $roleService = $container->get(RoleService::class);
        $sourceService = $container->get(SourceService::class);
        $userContextService = $container->get('UserContextService');

        /**
         * @var SourceCodeStringHelper $sourceCodeStringHelper;
         */
        $sourceCodeStringHelper = $container->get(SourceCodeStringHelper::class);

        $service = new ActeurHDRService();
        $service->setApplicationRoleService($roleService);
        $service->setSourceService($sourceService);
        $service->setUserContextService($userContextService);
        $service->setSourceCodeStringHelper($sourceCodeStringHelper);
        return $service;
    }
}