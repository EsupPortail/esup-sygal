<?php

namespace Application\Navigation;

use Interop\Container\ContainerInterface;

class NavigationFactoryFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $navigation = new ApplicationNavigationFactory();

        return $navigation($container, $requestedName, $options);
    }
}