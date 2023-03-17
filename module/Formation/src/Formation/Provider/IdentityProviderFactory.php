<?php

namespace Formation\Provider;

use Formation\Service\Session\SessionService;
use Psr\Container\ContainerInterface;
use Application\Service\Role\RoleService;
use UnicaenAuthentification\Service\UserContext;

class IdentityProviderFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IdentityProvider
    {
        /**
         * @var RoleService $roleService
         * @var SessionService $sessionService
         * @var UserContext $userService
         */
        $roleService = $container->get('RoleService');
        $sessionService = $container->get(SessionService::class);
        $userService = $container->get(UserContext::class);

        $service = new IdentityProvider();
        $service->setApplicationRoleService($roleService);
        $service->setSessionService($sessionService);
        $service->setServiceUserContext($userService);

        return $service;
    }
}