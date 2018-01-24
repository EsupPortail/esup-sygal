<?php

namespace Application\Event;

use Application\Service\UserContextService;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserAuthenticatedEventListenerFactory
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var UserContextService $userContextService */
        $userContextService = $serviceLocator->get('AuthUserContext');

        $listener = new UserAuthenticatedEventListener();
        $listener->setAuthUserContextService($userContextService);

        return $listener;
    }
}