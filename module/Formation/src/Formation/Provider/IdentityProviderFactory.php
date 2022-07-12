<?php

namespace Formation\Provider;

use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\RoleService;
use UnicaenAuth\Service\UserContext;

class IdentityProviderFactory
{
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
        $service->setRoleService($roleService);
        $service->setSessionService($sessionService);
        $service->setServiceUserContext($userService);
        return $service;
    }
}