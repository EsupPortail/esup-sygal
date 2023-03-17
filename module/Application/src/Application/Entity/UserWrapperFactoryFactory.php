<?php

namespace Application\Entity;

use Psr\Container\ContainerInterface;
use UnicaenAuthentification\Options\ModuleOptions;

/**
 * @author Unicaen
 */
class UserWrapperFactoryFactory
{
    /**
     * @param \Psr\Container\ContainerInterface $container
     * @return \Application\Entity\UserWrapperFactory
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UserWrapperFactory
    {
        /** @var ModuleOptions $authModuleOptions */
        $authModuleOptions = $container->get('unicaen-auth_module_options');

        $factory = new UserWrapperFactory();
        $factory->setModuleOptions($authModuleOptions);

        return $factory;
    }
}