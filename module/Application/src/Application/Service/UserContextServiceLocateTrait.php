<?php

namespace Application\Service;

use Interop\Container\ContainerInterface;

trait UserContextServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return UserContextService
     */
    public function locateUserContextService(ContainerInterface $container)
    {
        /** @var UserContextService $service */
        $service = $container->get('UserContextService');

        return $service;
    }
}