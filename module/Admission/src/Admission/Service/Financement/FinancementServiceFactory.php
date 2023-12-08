<?php

namespace Admission\Service\Financement;

use Admission\Service\Verification\VerificationService;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FinancementServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FinancementService
    {
        /**
         * @var RoleService $roleService
         * @var SourceService $sourceService
         * @var UserContextService $userContextService;
         */
        $roleService = $container->get(RoleService::class);
        $sourceService = $container->get(SourceService::class);
        $userContextService = $container->get('UserContextService');
        $verificationService = $container->get(VerificationService::class);

        /**
         * @var SourceCodeStringHelper $sourceCodeStringHelper;
         */
        $sourceCodeStringHelper = $container->get(SourceCodeStringHelper::class);

        $service = new FinancementService();
        $service->setRoleService($roleService);
        $service->setSourceService($sourceService);
        $service->setUserContextService($userContextService);
        $service->setSourceCodeStringHelper($sourceCodeStringHelper);
        $service->setVerificationService($verificationService);
        return $service;
    }
}