<?php

namespace Application\Navigation;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;

class NavigationFactoryFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var UserContextService $userContextService */
        $userContextService = $container->get('UserContextService');
        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        $navigation = new ApplicationNavigationFactory();
        $navigation->setUserContextService($userContextService);
        $navigation->setTheseService($theseService);

        return $navigation->__invoke($container, $requestedName, $options);
    }
}