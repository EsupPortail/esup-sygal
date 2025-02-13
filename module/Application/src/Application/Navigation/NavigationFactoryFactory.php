<?php

namespace Application\Navigation;

use HDR\Service\HDRService;
use These\Service\These\TheseService;
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
        /** @var HDRService $hdrService */
        $hdrService = $container->get(HDRService::class);

        $navigation = new ApplicationNavigationFactory();
        $navigation->setUserContextService($userContextService);
        $navigation->setTheseService($theseService);
        $navigation->setHDRService($hdrService);

        return $navigation->__invoke($container, $requestedName, $options);
    }
}