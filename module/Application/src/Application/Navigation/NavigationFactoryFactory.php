<?php

namespace Application\Navigation;

use Interop\Container\ContainerInterface;

class NavigationFactoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $navigation = new ApplicationNavigationFactory();
        return $navigation->createService($container);
    }
}