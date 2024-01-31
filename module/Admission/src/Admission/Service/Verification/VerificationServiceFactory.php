<?php

namespace Admission\Service\Verification;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class VerificationServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): VerificationService
    {
        /**
         * @var UserContextService $userContextService;
         */
        $userContextService = $container->get('UserContextService');

        $service = new VerificationService();
        $service->setUserContextService($userContextService);
        return $service;
    }
}